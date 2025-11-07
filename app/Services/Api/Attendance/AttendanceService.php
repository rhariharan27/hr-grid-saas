<?php

namespace App\Services\Api\Attendance;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Enums\AttendanceLogType;
use App\Http\Requests\Api\Attendance\CheckInOutRequest;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AttendanceService implements IAttendance
{

  public function checkInOut(CheckInOutRequest $data): JsonResponse
  {
    try {

      $userId = auth()->id();

      $isMultiCheckInOutEnabled = $this->isMultiCheckInOutEnabled();

      $todayAttendance = Attendance::where('user_id', $userId)
        ->whereDate('created_at', Carbon::today())
        ->first();

      if (!$todayAttendance) {
        //Fresh check in for the day
        $attendance = new Attendance();
        $attendance->user_id = $userId;
        $attendance->check_in_time = now()->toTimeString();
        $attendance->shift_id = auth()->user()->shift_id;
        $attendance->date = now();
        $attendance->created_by_id = auth()->id();
        $attendance->save();

        $this->takeAttendanceLog($attendance->id, AttendanceLogType::CHECK_IN, $data);
        return Success::response('Checked In');
      } else {
        $attendanceLog = AttendanceLog::where('attendance_id', $todayAttendance->id)->latest()->first();
        if ($attendanceLog->type == AttendanceLogType::CHECK_IN) {
          //Check out
          if (!$isMultiCheckInOutEnabled) {
            $todayAttendance->check_out_time = Carbon::now();
            $todayAttendance->save();
          }
          $this->takeAttendanceLog($todayAttendance->id, AttendanceLogType::CHECK_OUT, $data);
          return Success::response('Checked Out');
        } else {

          if (!$isMultiCheckInOutEnabled) {
            return Error::response('You have already checked out');
          }

          //Check in
          $this->takeAttendanceLog($todayAttendance->id, AttendanceLogType::CHECK_IN, $data);
          return Success::response('Checked In again');
        }
      }
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return Error::response('Something went wrong, please try again');
    }
  }

  private function isMultiCheckInOutEnabled(): bool
  {
    try {
      return auth()->user()->roles()->first()->is_multiple_check_in_enabled;
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return false;
    }
  }

  private function takeAttendanceLog(int $attendanceId, AttendanceLogType $type, CheckInOutRequest $data): void
  {
    //Take Attendance Log
    AttendanceLog::create([
      'attendance_id' => $attendanceId,
      'created_by_id' => auth()->id(),
      'type' => $type,
      'latitude' => $data['latitude'],
      'longitude' => $data['longitude'],
      'altitude' => $data['altitude'],
      'speed' => $data['speed'] ?? null,
      'horizontalAccuracy' => $data['horizontalAccuracy'] ?? null,
      'verticalAccuracy' => $data['verticalAccuracy'] ?? null,
      'course' => $data['course'] ?? null,
      'courseAccuracy' => $data['courseAccuracy'] ?? null,
      'speedAccuracy' => $data['speedAccuracy'] ?? null,
      'address' => $data['address'] ?? null,
    ]);
  }

  public function getStatus(): JsonResponse
  {
    $user = auth()->user();

    $shiftInfo = [
      'shift_start' => Carbon::parse($user->shift->start_time)->format('h:i A'),
      'shift_end' => Carbon::parse($user->shift->end_time)->format('h:i A'),
    ];

    $response = [];

    $isMultiCheckInOutEnabled = $this->isMultiCheckInOutEnabled();

    $response['shiftInfo'] = $shiftInfo;
    $response['isMultiCheckInOutEnabled'] = $isMultiCheckInOutEnabled;

    if ($user->isOnLeave()) {

      return Success::response(['status' => 'on leave']);

    } else {

      $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('created_at', Carbon::today())
        ->first();

      if (!$attendance) {
        $response['status'] = 'new';
      } else {

        if ($isMultiCheckInOutEnabled) {
          $attendanceLogs = AttendanceLog::where('attendance_id', $attendance->id)->latest()->first();

          if ($attendanceLogs->type == AttendanceLogType::CHECK_IN) {
            $response['status'] = 'checked in';
          } else {
            $response['status'] = 'checked out';
          }

        } else {
          //Single check in out
          if ($attendance->check_out_time) {
            $response['status'] = 'checked out';
          } else {
            $response['status'] = 'checked in';
          }
        }
      }


      return Success::response($response);
    }
  }

  public function isCheckedIn(): bool
  {
    $attendance = Attendance::where('user_id', auth()->id())
      ->whereDate('created_at', Carbon::today())
      ->first();

    if (!$attendance) {
      return false;
    }

    $attendanceLog = AttendanceLog::where('attendance_id', $attendance->id)->latest()->first();

    return $attendanceLog->type == AttendanceLogType::CHECK_IN;
  }
}
