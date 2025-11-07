<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LeaveType extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'leave_types';

  protected $fillable = [
    'name',
    'per_month',
    'per_year',
    'code',
    'notes',
    'is_proof_required',
    'status',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];

  protected $casts = [
    'status' => Status::class,
    'is_proof_required' => 'boolean',
    'per_month' => 'integer',
    'per_year' => 'integer',
  ];
}
