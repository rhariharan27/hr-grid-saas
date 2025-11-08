<?php

namespace Modules\Payroll\app\Console;


use App\Enums\LeaveRequestStatus;
use App\Enums\Status;
use App\Enums\UserAccountStatus;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateAttendance extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'payroll:attendance-calculate {--date= : Calculate for a specific date (YYYY-MM-DD)}';
  protected $description = 'Calculate and consolidate daily attendance details based on logs, shifts, leave, and holidays.';


  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Determine target date (yesterday or specified)
    $targetDate = $this->option('date') ? Carbon::parse($this->option('date'))->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');
    $targetCarbonDate = Carbon::parse($targetDate);

    $this->info("Starting attendance calculation for date: {$targetDate}");
    Log::channel('payroll')->info("Starting attendance calculation for date: {$targetDate}"); // Use dedicated channel maybe


    Tenant::all()->runForEach(function () use ($targetDate, $targetCarbonDate) {
      $tenantId = tenant('id');
      Log::channel('payroll')->info("Processing Tenant ID: {$tenantId}");

      // Get active users WITH their shift assigned for the target date
      // Assuming user model has a shift() relationship
      $activeUsers = User::where('status', UserAccountStatus::ACTIVE)
        ->with(['shift' => function ($query) use ($targetCarbonDate) {
          // Load the shift active on the target date
          $query->where('start_date', '<=', $targetCarbonDate)
            ->where(function ($q) use ($targetCarbonDate) {
              $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $targetCarbonDate);
            })
            ->where('status', Status::ACTIVE); // Assuming Status Enum on Shift
        }])
        ->select('id', 'shift_id', 'tenant_id') // Select only needed fields + FKs
        ->get();

      // Get Holidays for the target date
      $holiday = Holiday::where('date', $targetDate)
        ->where('status', Status::ACTIVE->value)
        ->first();

      if ($holiday) {
        Log::channel('payroll')->info("Date {$targetDate} is a holiday: {$holiday->name}");
      }

      foreach ($activeUsers as $user) {
        $shift = $user->shift; // Get the loaded shift

        // Determine if user should have worked
        $isScheduledWorkday = false;
        if ($shift) {
          $dayOfWeek = $targetCarbonDate->dayOfWeek; // 0 = Sunday, 6 = Saturday
          $workDays = $shift->work_days_array; // Assumes method exists in Shift model
          $isScheduledWorkday = $workDays[$dayOfWeek] ?? false;
        }

        // Check for Approved Leave covering the target date
        $onLeave = LeaveRequest::where('user_id', $user->id)
          ->where('status', LeaveRequestStatus::APPROVED->value)
          ->where('from_date', '<=', $targetDate)
          ->where('to_date', '>=', $targetDate)
          ->first();
        // TODO: Refine leave check for half-days if applicable

        $attendanceStatus = null;
        $calculatedHours = [
          'working_hours' => 0,
          'late_hours' => 0,
          'early_hours' => 0,
          'overtime_hours' => 0,
          'first_in' => null,
          'last_out' => null
        ];

        // Determine initial status based on schedule/leave/holiday
        if ($onLeave) {
          $attendanceStatus = 'leave';
          // TODO: Differentiate paid/unpaid leave if needed for payroll
        } elseif ($holiday) {
          $attendanceStatus = 'holiday'; // Add 'holiday' to Attendance status enum if needed
        } elseif (!$shift || !$isScheduledWorkday) {
          $attendanceStatus = 'off_day'; // Add 'off_day' status if needed
        } // If scheduled to work and not on leave/holiday, check logs
        elseif ($isScheduledWorkday) {
          // Fetch logs for this specific user and date
          $logs = AttendanceLog::where('created_by_id', $user->id) // Assuming created_by_id is the user
            ->whereDate('created_at', $targetDate)
            ->orderBy('created_at', 'asc')
            ->get();

          if ($logs->isEmpty()) {
            $attendanceStatus = 'absent';
          } else {
            // --- Process Logs if Present ---
            $firstCheckIn = $logs->firstWhere('type', 'check_in');
            $lastCheckOut = $logs->where('type', 'check_out')->last();

            if ($firstCheckIn && $lastCheckOut && $lastCheckOut->created_at > $firstCheckIn->created_at) {
              $attendanceStatus = 'present'; // Base status

              $calculatedHours['first_in'] = $firstCheckIn->created_at;
              $calculatedHours['last_out'] = $lastCheckOut->created_at;

              // --- Accurate Hour Calculation ---
              $totalDurationMinutes = 0;
              $totalBreakMinutes = 0;
              $currentCheckInTime = null;
              $currentBreakStartTime = null;

              foreach ($logs as $log) {
                $logTime = Carbon::parse($log->created_at);
                switch ($log->type) {
                  case 'check_in':
                    if ($currentCheckInTime === null) {
                      $currentCheckInTime = $logTime;
                    }
                    break;
                  case 'check_out':
                    if ($currentCheckInTime !== null) {
                      $totalDurationMinutes += $currentCheckInTime->diffInMinutes($logTime);
                      $currentCheckInTime = null; // Reset after check-out
                    }
                    break;
                  case 'break_start':
                    if ($currentBreakStartTime === null) {
                      $currentBreakStartTime = $logTime;
                    }
                    break;
                  case 'break_end':
                    if ($currentBreakStartTime !== null) {
                      $totalBreakMinutes += $currentBreakStartTime->diffInMinutes($logTime);
                      $currentBreakStartTime = null; // Reset after break ends
                    }
                    break;
                }
              }
              // If shift uses fixed break time instead of logs
              if ($shift->is_break_enabled && !$logs->whereIn('type', ['break_start', 'break_end'])->count()) {
                $totalBreakMinutes = $shift->break_time ?? 0;
              }

              Log::channel('payroll')->info("Attendance status: {$attendanceStatus}");
              Log::channel('payroll')->info("Total Duration Minutes: {$totalDurationMinutes}, Total Break Minutes: {$totalBreakMinutes}");
              $calculatedHours['working_hours'] = max(0, round(($totalDurationMinutes - $totalBreakMinutes) / 60, 2));

              Log::channel('payroll')->info("Calculated Working Hours: {$calculatedHours['working_hours']}");

              // --- Late/Early/OT Calculation ---
              $shiftStartTime = Carbon::parse($targetDate . ' ' . $shift->start_time->format('H:i:s')); // Combine date + shift time
              $shiftEndTime = Carbon::parse($targetDate . ' ' . $shift->end_time->format('H:i:s'));
              // Handle overnight shifts if end time is before start time
              if ($shiftEndTime->lessThan($shiftStartTime)) {
                $shiftEndTime->addDay();
              }
              // TODO: Add Grace Period from settings/shift
              $gracePeriod = 0; // Example: $settings->grace_period ?? 0;

              if ($calculatedHours['first_in']->gt($shiftStartTime->addMinutes($gracePeriod))) {
                $calculatedHours['late_hours'] = round($calculatedHours['first_in']->diffInMinutes($shiftStartTime) / 60, 2);
              }
              if ($calculatedHours['last_out']->lt($shiftEndTime->subMinutes($gracePeriod))) {
                $calculatedHours['early_hours'] = round($shiftEndTime->diffInMinutes($calculatedHours['last_out']) / 60, 2);
              }
              $expectedWorkHours = $shift->scheduled_work_hours_per_day ?? 0; // Use helper
              if ($shift->is_over_time_enabled && $calculatedHours['working_hours'] > $expectedWorkHours) {
                $calculatedHours['overtime_hours'] = round($calculatedHours['working_hours'] - $expectedWorkHours, 2);
              }

              // Determine final status based on hours (e.g., Half-day)
              if ($expectedWorkHours > 0 && $calculatedHours['working_hours'] < ($expectedWorkHours / 2)) {
                // Threshold check might need adjustment
                $attendanceStatus = 'half-day'; // Add 'half-day' to Attendance status enum
              }
            } else {
              // Incomplete data (e.g., only check-in or only check-out)
              $attendanceStatus = 'absent'; // Or a specific 'incomplete' status
              Log::warning("Incomplete logs for user {$user->id} on {$targetDate}. Marked as {$attendanceStatus}.");
            }
          } // End if logs not empty
        } // End if scheduled work day

        // --- Save/Update Attendance Record ---
        if ($attendanceStatus !== null) { // Only update if a status was determined
          $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', $targetDate)
            ->first();

          $dataToSave = [
            'status' => $attendanceStatus,
            'check_in_time' => $calculatedHours['first_in'],
            'check_out_time' => $calculatedHours['last_out'], // Store actual last check-out log time (or null)
            'working_hours' => round($calculatedHours['working_hours'], 2),
            'late_hours' => round($calculatedHours['late_hours'], 2),
            'early_hours' => round($calculatedHours['early_hours'], 2),
            'overtime_hours' => round($calculatedHours['overtime_hours'], 2),
          ];

          if ($attendance) {
            // --- UPDATE existing record ---
            // Avoid manually setting created_at on update
            $attendance->update($dataToSave);
            Log::channel('payroll')->info("Attendance record UPDATED for User: {$user->id}, Date: {$targetDate}, Status: {$attendanceStatus}");
          } else {
            // --- CREATE new record ---
            $dataToSave['user_id'] = $user->id;
            // IMPORTANT: Let Laravel handle created_at on creation.
            // DO NOT set 'created_at' => $targetDate here.
            // Rely on whereDate('created_at', $targetDate) for lookup.
            // This means the created_at timestamp will reflect when the command *ran*,
            // not necessarily the start of the attendance day.
            Attendance::create($dataToSave);
            Log::channel('payroll')->info("Attendance record CREATED for User: {$user->id}, Date: {$targetDate}, Status: {$attendanceStatus}");
          }

          Log::channel('payroll')->info("Attendance record updated/created for User: {$user->id}, Date: {$targetDate}, Status: {$attendanceStatus}, Hours: " . json_encode($calculatedHours));
        } else {
          Log::channel('payroll')->info("Skipping attendance record update for User: {$user->id}, Date: {$targetDate} (Not a scheduled workday or no status determined).");
        }
      }
    });

    $this->info('Attendance calculations completed successfully!');
    Log::info('Attendance calculations completed successfully!');
  }
}
