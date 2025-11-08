<?php

namespace Modules\Payroll\app\Models;

use App\Models\User;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Enums\PayrollRecordStatus;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class PayrollRecord extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'payroll_records';

  protected $fillable = [
    'user_id',
    'payroll_cycle_id',
    'period',
    'basic_salary',
    'overtime_pay' .
      'gross_salary',
    'net_salary',
    'tax_amount',
    'status',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
    'cancel_remarks'
  ];


  protected $casts = [
    'basic_salary' => 'float',
    'overtime_pay' => 'float',
    'gross_salary' => 'float',
    'net_salary' => 'float',
    'tax_amount' => 'float',
    'status' => PayrollRecordStatus::class,
  ];

  public function payrollCycle()
  {
    return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function payrollAdjustments()
  {
    return $this->hasMany(PayrollAdjustment::class, 'payroll_record_id');
  }

  public function payrollAdjustmentLogs()
  {
    return $this->hasMany(PayrollAdjustmentLog::class, 'payroll_record_id');
  }
}
