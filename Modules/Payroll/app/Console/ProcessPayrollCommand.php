<?php

namespace Modules\Payroll\app\Console;

use App\Enums\LeaveRequestStatus;
use App\Enums\Status;
use App\Enums\UserAccountStatus;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\app\Models\PayrollAdjustment;
use Modules\Payroll\app\Models\PayrollAdjustmentLog;
use Modules\Payroll\app\Models\PayrollCycle;
use Modules\Payroll\app\Models\PayrollRecord;
use Modules\Payroll\app\Models\Payslip;

class ProcessPayrollCommand extends Command
{
  protected $signature = 'payroll:process {--period= : The payroll period (e.g., "January 2025")}';
  protected $description = 'Process payroll for a specific period, including global and employee-specific adjustments';

  public function handle()
  {
    try {
      $this->info('Starting payroll processing...');
      Log::info('Starting payroll processing...');

      $periodOption = $this->option('period');
      $period = $periodOption ?: Carbon::now()->subMonthNoOverflow()->format('F Y'); // Default to previous month

      $this->info("Processing payroll for period: $period");
      Log::info("Processing payroll for period: $period");

      // --- Create or Retrieve Payroll Cycle ---
      $startDate = Carbon::parse('first day of ' . $period)->startOfDay();
      $endDate = Carbon::parse('last day of ' . $period)->endOfDay();
      // Use a consistent code for the cycle, e.g., YYYY-MM
      $cycleCode = Carbon::parse($period)->format('Y-m');

      $payrollCycle = PayrollCycle::updateOrCreate(
        ['code' => $cycleCode], // Use code and tenant as unique keys
        [
          'name' => $period . ' Payroll',
          'frequency' => 'monthly', // Or read from settings
          'pay_period_start' => $startDate,
          'pay_period_end' => $endDate,
          // Calculate pay date based on rules/settings (e.g., 5th of next month)
          'pay_date' => $endDate->copy()->addDays(5)->startOfDay(),
          'status' => 'pending', // Start as pending
          // 'created_by_id' => ... // Set if needed
        ]
      );

      // If the cycle already existed but wasn't pending, maybe force update status?
      if ($payrollCycle->status !== 'pending' && $payrollCycle->status !== 'cancelled') {
        $payrollCycle->status = 'pending'; // Or 'processing'?
        $payrollCycle->save();
      }

      $this->info("Using Payroll Cycle: {$payrollCycle->name} (ID: {$payrollCycle->id})");
      Log::info("Using Payroll Cycle: {$payrollCycle->name} (ID: {$payrollCycle->id})");

      // --- Get Period Info ---
      $daysInPeriod = $startDate->daysInMonth; // Total calendar days
      $dateRange = CarbonPeriod::create($startDate, $endDate);

      // Get Holidays within the period
      $holidayDates = Holiday::whereBetween('date', [$startDate, $endDate])
        ->where('status', Status::ACTIVE->value) // Assuming Status Enum
        ->pluck('date')
        ->map(fn($date) => $date->format('Y-m-d')) // Format as string for comparison
        ->flip();

      // Fetch all active users
      $users = User::where('status', UserAccountStatus::ACTIVE->value)
        ->with(['shift'])
        ->get();

      foreach ($users as $user) {
        DB::beginTransaction();
        try {

          // Skip if already processed for this cycle
          if (PayrollRecord::where('user_id', $user->id)->where('payroll_cycle_id', $payrollCycle->id)->exists()) {
            Log::info("Skipping already existing payroll record for User {$user->id} in Cycle {$payrollCycle->id}.");
            DB::commit();
            continue;
          }

          // --- Data Gathering per User ---
          $userShift = $user->shift; // User's primary shift (can be more complex)
          $baseSalaryMonthly = $user->base_salary ?? 0; // Get from User or Compensation model
          $overtimeRate = $user->overtime_rate ?? 0; // Get from User or Compensation model

          // Get Attendance Summary for the period
          $attendanceSummary = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', '>=', $startDate->format('Y-m-d'))
            ->whereDate('created_at', '<=', $endDate->format('Y-m-d'))
            ->selectRaw('status, COUNT(*) as count, SUM(working_hours) as total_working, SUM(overtime_hours) as total_overtime')
            ->groupBy('status')
            ->get()
            ->keyBy('status'); // Key by status for easy access

          $presentDays = $attendanceSummary->get('present')?->count ?? 0;
          $halfDays = $attendanceSummary->get('half-day')?->count ?? 0;
          $absentDays = $attendanceSummary->get('absent')?->count ?? 0;
          // Leave and Holiday days are counted below, might overlap with attendance status depending on calc command logic

          // --- Calculate Scheduled Work Days & Leave Days ---
          $scheduledWorkDays = 0;
          $periodLeaveDays = 0; // Total leave days *within* the pay period
          $paidLeaveDays = 0;
          $unpaidLeaveDays = 0;


          if ($userShift) {
            $workDaysArray = $userShift->work_days_array; // Assumes accessor exists
            foreach ($dateRange as $date) {
              $dateStr = $date->format('Y-m-d');
              // Check if it's a scheduled workday AND not a holiday
              if (($workDaysArray[$date->dayOfWeek] ?? false) && !$holidayDates->has($dateStr)) {
                $scheduledWorkDays++;
                // Check if user was on approved leave on this specific workday
                $leaveRecord = LeaveRequest::where('user_id', $user->id)
                  ->where('status', LeaveRequestStatus::APPROVED->value)
                  ->whereDate('from_date', '<=', $dateStr)
                  ->whereDate('to_date', '>=', $dateStr)
                  ->first(); // Assuming one leave record per day max
                if ($leaveRecord) {
                  $periodLeaveDays++;
                  // TODO: Check leave type (paid/unpaid) based on $leaveRecord->leaveType relationship or field
                  $isPaidLeave = true; // Placeholder - determine if paid
                  if ($isPaidLeave) {
                    $paidLeaveDays++;
                  } else {
                    $unpaidLeaveDays++;
                  }
                }
              }
            }
          } else {
            Log::warning("User {$user->id} has no valid shift assigned for period {$period}. Skipping detailed calc.");
          }
          // --- Calculate Salary Components ---
          $dailyRate = ($scheduledWorkDays > 0) ? ($baseSalaryMonthly / $scheduledWorkDays) : 0; // Rate per scheduled day
          $payableDays = $presentDays + $paidLeaveDays + ($halfDays * 0.5); // Days to be paid for

          // Option 1: Prorated Base Salary
          $earnedBaseSalary = $dailyRate * $payableDays;
          // Option 2: Use Summed Working Hours (if more appropriate)
          // $totalWorkingHours = $attendanceSummary->sum('total_working');
          // $hourlyRate = ($userShift && $userShift->scheduled_work_hours_per_day > 0) ? ($dailyRate / $userShift->scheduled_work_hours_per_day) : 0;
          // $earnedBaseSalary = $totalWorkingHours * $hourlyRate; // Doesn't account for paid leave easily

          $totalOvertimeHours = $attendanceSummary->sum('total_overtime');
          $overtimePay = round($totalOvertimeHours * $overtimeRate, 2);


          // Fetch Adjustments (Benefits/Deductions)
          $adjustments = PayrollAdjustment::where(function ($query) use ($user) {
            $query->where('applicability', 'global')
              ->orWhere('user_id', $user->id);
          })->get();
          $totalDeductions = 0;
          $totalBenefits = 0;
          $adjustmentsLogs = [];
          $grossSalaryForAdjustments = $earnedBaseSalary + $overtimePay; // Calculate % adjustments based on earned base + OT

          foreach ($adjustments as $adjustment) {
            $amount = $adjustment->amount;
            if ($adjustment->percentage) { // Calculate % based on current gross (or base only?) - Check policy
              $amount = ($grossSalaryForAdjustments * $adjustment->percentage) / 100;
            }
            if ($adjustment->type === 'deduction') {
              $totalDeductions += $amount;
            } elseif ($adjustment->type === 'benefit') {
              $totalBenefits += $amount;
            }
            $adjustmentsLogs[] = [
              'payroll_adjustment_id' => $adjustment->id,
              'name' => $adjustment->name,
              'type' => $adjustment->type,
              'applicability' => $adjustment->applicability,
              'amount' => $amount,
              'percentage' => $adjustment->percentage,
              'user_id' => $adjustment->user_id,
              'log_message' => "Applied adjustment during payroll processing.",
            ];
          }

          // Calculate Gross, Net, Tax (assuming tax is part of deductions for now)

          $grossSalary = $earnedBaseSalary + $overtimePay + $totalBenefits;
          $taxAmount = 0; // TODO: Implement Tax Calculation Logic
          $totalDeductions += $taxAmount;
          $netSalary = $grossSalary - $totalDeductions;


          // --- Create Records ---
          $payrollRecord = PayrollRecord::create([
            'user_id' => $user->id,
            'payroll_cycle_id' => $payrollCycle->id,
            'period' => $period,
            'basic_salary' => $earnedBaseSalary,
            'overtime_pay' => $overtimePay,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'tax_amount' => $taxAmount,
            'status' => 'pending',
          ]);

          if (!empty($adjustmentsLogs)) {
            PayrollAdjustmentLog::insert(array_map(fn($log) => array_merge($log, ['payroll_record_id' => $payrollRecord->id]), $adjustmentsLogs));
          }

          Payslip::create([
            'user_id' => $user->id,
            'payroll_record_id' => $payrollRecord->id,
            'code' => 'PSL-' . $payrollRecord->id . '-' . $user->id, // Unique code
            'basic_salary' => $earnedBaseSalary, // Store EARNED base
            'total_deductions' => $totalDeductions,
            'total_benefits' => $totalBenefits,
            'net_salary' => $netSalary,
            'status' => 'generated',
            // --- Populate Attendance Summary Accurately ---
            'total_worked_days' => $presentDays + ($halfDays * 0.5), // Approximation
            'total_absent_days' => $absentDays,
            'total_leave_days' => $periodLeaveDays, // Total leave days within period
            'total_late_days' => $user->attendances()->whereBetween('attendance_date', [$startDate, $endDate])->where('late_hours', '>', 0)->count(), // Example
            'total_early_checkout_days' => $user->attendances()->whereBetween('attendance_date', [$startDate, $endDate])->where('early_hours', '>', 0)->count(), // Example
            'total_overtime_days' => $user->attendances()->whereBetween('attendance_date', [$startDate, $endDate])->where('overtime_hours', '>', 0)->count(), // Example
            'total_holidays' => $holidayDates->count(), // Approx if holidays affect work schedule calc
            'total_weekends' => $daysInPeriod - $scheduledWorkDays - $holidayDates->count(), // Approx
            'total_working_days' => $scheduledWorkDays, // Total scheduled days
          ]);

          DB::commit();
          $this->info("Processed payroll for User ID: {$user->id}");
        } catch (Exception $e) {
          DB::rollBack();
          $this->error("Failed to process payroll for {$user->first_name} {$user->last_name}: {$e->getMessage()}");
          Log::info("Failed to process payroll for {$user->first_name} {$user->last_name}: {$e->getMessage()}");
          Log::info($e->getMessage());
        }
      }

      // Update Payroll Cycle Status
      $payrollCycle->status = 'processed';
      $payrollCycle->save();

      $this->info("Payroll processing completed for period: $period");
      Log::info("Payroll processing completed for period: $period");
    } catch (Exception $e) {
      $this->error("An error occurred: {$e->getMessage()}");
      Log::error($e->getMessage());
    }
  }
}
