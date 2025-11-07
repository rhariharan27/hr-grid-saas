<?php

namespace App\Http\Controllers\tenant;

use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Carbon\Carbon;
use Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
  public function index()
  {
    $users = User::where('status', UserAccountStatus::ACTIVE)
      ->get();

    $attendances = Attendance::where('created_at', date('Y-m-d'))
      ->first();

    $logs = AttendanceLog::get();

    return view('tenant.attendance.index', [
      'users' => $users,
      'attendances' => $attendances ?? [],
      'attendanceLogs' => $logs ?? [],
    ]);
  }

  public function indexAjax(Request $request)
  {
    $query = Attendance::query()
      ->with([
        'user:id,first_name,last_name,code,profile_picture',
        'attendanceLogs'
      ]);

    //User filter
    if ($request->has('userId') && $request->input('userId')) {
      Log::info('User ID: ' . $request->input('userId'));
      $query->where('user_id', $request->input('userId'));
    }

    // Date filter
    $filterDate = Carbon::today();
    if ($request->has('date') && $request->input('date')) {
      Log::info('Date: ' . $request->input('date'));
      try {
        $filterDate = Carbon::parse($request->input('date'));
        $query->whereDate('created_at', $filterDate);
      } catch (\Exception $e) {
        Log::error('Invalid date format provided: ' . $request->input('date'));
        $query->whereDate('created_at', $filterDate);
      }
    } else {
      $query->whereDate('created_at', $filterDate);
    }

    return DataTables::of($query)
      ->addColumn('id', function ($attendance) {
        return $attendance->id;
      })
      ->addColumn('user', function ($attendance) {
        if (!$attendance->user) {
          return '<span class="text-muted">N/A</span>';
        }
        $employeeViewUrl = url('employees/view/' . $attendance->user_id);
        if ($attendance->user->profile_picture) {
          $profileOutput = '<img src="' . tenant_asset(Constants::BaseFolderEmployeeProfileWithSlash . $attendance->user->profile_picture) . '"  alt="Avatar" class="avatar rounded-circle " />';
        } else {
          $profileOutput = '<span class="avatar-initial rounded-circle bg-label-info">' . $attendance->user->getInitials() . '</span>';
        }

        return '<div class="d-flex justify-content-start align-items-center user-name">' .
          '<div class="avatar-wrapper">' .
          '<div class="avatar avatar-sm me-4">' .
          $profileOutput .
          '</div>' .
          '</div>' .
          '<div class="d-flex flex-column">' .
          '<a href="' .
          $employeeViewUrl .
          '" class="text-heading text-truncate"><span class="fw-medium">' .
          $attendance->user->getFullName() .
          '</span></a>' .
          '<small>' .
          $attendance->user->code .
          '</small>' .
          '</div>' .
          '</div>';
      })
      ->addColumn('first_check_in', function ($attendance) {
        $firstCheckIn = $attendance->attendanceLogs
          ->where('type', 'check_in')
          ->sortBy('created_at')
          ->first();
        return $firstCheckIn ? $firstCheckIn->created_at->format('h:i A') : '<span class="text-muted">--:--</span>';
      })
      ->addColumn('last_check_out', function ($attendance) {
        $lastCheckOut = $attendance->attendanceLogs
          ->where('type', 'check_out')
          ->sortByDesc('created_at')
          ->first();
        return $lastCheckOut ? $lastCheckOut->created_at->format('h:i A') : '<span class="text-muted">--:--</span>';
      })
      ->addColumn('duration', function ($attendance) {
        $firstCheckIn = $attendance->attendanceLogs
          ->where('type', 'check_in')
          ->sortBy('created_at')
          ->first();
        $lastCheckOut = $attendance->attendanceLogs
          ->where('type', 'check_out')
          ->sortByDesc('created_at')
          ->first();

        if ($firstCheckIn && $lastCheckOut && $lastCheckOut->created_at->gt($firstCheckIn->created_at)) {
          $duration = $firstCheckIn->created_at->diff($lastCheckOut->created_at);
          return $duration->format('%H:%I');
        }
        return '<span class="text-muted">--:--</span>';
      })
      ->addColumn('shift', function ($attendance) {
        return $attendance->shift ? $attendance->shift->name : '<span class="text-muted">N/A</span>';
      })
      ->addColumn('status', function ($attendance) {
        return $attendance->status ? ucwords(str_replace('_', ' ', $attendance->status)) : '<span class="text-muted">N/A</span>';
      })
      ->addColumn('log_count', function ($attendance) {
        return $attendance->attendanceLogs->count();
      })
      ->rawColumns(['user', 'first_check_in', 'last_check_out', 'duration', 'shift', 'status'])
      ->make(true);
  }
}
