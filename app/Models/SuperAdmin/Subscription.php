<?php

namespace App\Models\SuperAdmin;

use App\Enums\SubscriptionStatus;
use App\Models\User;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Subscription extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'subscriptions';

  protected $fillable = [
    'user_id',
    'plan_id',
    'users_count',
    'total_price',
    'start_date',
    'end_date',
    'status',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
    'per_user_price',
    'additional_users',
  ];
  protected $casts = [
    'users_count' => 'integer',
    'total_price' => 'float',
    'status' => SubscriptionStatus::class
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }
}
