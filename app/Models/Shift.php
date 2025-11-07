<?php

namespace App\Models;

use App\Enums\ShiftType;
use App\Enums\Status;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Shift extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'shifts';

  protected $fillable = [
    'name',
    'code',
    'notes',
    'start_date',
    'end_date',
    'start_time',
    'end_time',
    'sunday',
    'monday',
    'tuesday',
    'wednesday',
    'thursday',
    'friday',
    'saturday',
    'is_infinite',
    'over_time_threshold',
    'is_default',
    'is_over_time_enabled',
    'is_break_enabled',
    'break_time',
    'shift_type',
    'status',
    'timezone',
    'created_by_id',
    'updated_by_id',
    'tenant_id',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'start_time' => 'datetime:H:i:s',
    'end_time' => 'datetime:H:i:s',
    'sunday' => 'boolean',
    'monday' => 'boolean',
    'tuesday' => 'boolean',
    'wednesday' => 'boolean',
    'thursday' => 'boolean',
    'friday' => 'boolean',
    'saturday' => 'boolean',
    'is_infinite' => 'boolean',
    'is_default' => 'boolean',
    'is_over_time_enabled' => 'boolean',
    'is_break_enabled' => 'boolean',
    'shift_type' => ShiftType::class,
    'status' => Status::class
  ];

  /**
   * Get the scheduled work days as a simple array indexed by Carbon dayOfWeek (0=Sun, 6=Sat).
   * Accessor: $shift->work_days_array
   *
   * @return Attribute
   */
  protected function workDaysArray(): Attribute
  {
    return Attribute::make(
      get: fn() => [
        0 => $this->sunday,    // Carbon::SUNDAY
        1 => $this->monday,    // Carbon::MONDAY
        2 => $this->tuesday,   // Carbon::TUESDAY
        3 => $this->wednesday, // Carbon::WEDNESDAY
        4 => $this->thursday,  // Carbon::THURSDAY
        5 => $this->friday,    // Carbon::FRIDAY
        6 => $this->saturday,  // Carbon::SATURDAY
      ]
    );
  }

  /**
   * Calculate the expected net working hours per day for this shift.
   * Considers start/end time and enabled break time. Handles overnight shifts.
   * Accessor: $shift->scheduled_work_hours_per_day
   *
   * @return Attribute
   */
  protected function scheduledWorkHoursPerDay(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->start_time || !$this->end_time) {
          return 0; // Cannot calculate without start/end times
        }

        // Use Carbon to parse times - date part doesn't matter for diff calculation here
        $startTime = Carbon::parse($this->start_time->format('H:i:s'));
        $endTime = Carbon::parse($this->end_time->format('H:i:s'));

        // Handle overnight shift (where end time is on the next day)
        if ($endTime->lessThan($startTime)) {
          $endTime->addDay();
        }

        // Calculate total duration in minutes
        $totalMinutes = $startTime->diffInMinutes($endTime);

        // Subtract break time if enabled
        $breakMinutes = ($this->is_break_enabled && $this->break_time > 0) ? $this->break_time : 0;

        // Calculate net hours
        $netMinutes = max(0, $totalMinutes - $breakMinutes);
        return round($netMinutes / 60, 2); // Return hours as float
      }
    );
  }

}
