<?php

namespace App\Models\SuperAdmin;

use App\Enums\PlanDurationType;
use App\Enums\Status;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Plan extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'plans';

  protected $fillable = [
    'name',
    'base_price',
    'per_user_price',
    'duration',
    'description',
    'duration_type',
    'included_users',
    'modules',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];

  protected $casts = [
    'base_price' => 'float',
    'per_user_price' => 'float',
    'included_users' => 'integer',
    'modules' => 'json',
    'duration' => 'integer',
    'duration_type' => PlanDurationType::class,
    'status' => Status::class
  ];

  public function orders()
  {
    return $this->hasMany(Order::class);
  }

  public function offlineRequests()
  {
    return $this->hasMany(OfflineRequest::class);
  }

}
