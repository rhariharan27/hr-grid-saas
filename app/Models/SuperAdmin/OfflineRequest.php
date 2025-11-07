<?php

namespace App\Models\SuperAdmin;

use App\Enums\OfflineRequestStatus;
use App\Enums\OrderType;
use App\Models\User;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OfflineRequest extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'offline_requests';

  protected $fillable = [
    'user_id',
    'plan_id',
    'order_id',
    'status',
    'reject_reason',
    'cancelled_reason',
    'approval_reason',
    'notes',
    'additional_users',
    'per_user_price',
    'amount',
    'discount_amount',
    'total_amount',
    'type',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];
  protected $casts = [
    'status' => OfflineRequestStatus::class,
    'type' => OrderType::class,
    'approved_at' => 'datetime',
    'cancelled_at' => 'datetime',
    'rejected_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function order()
  {
    return $this->belongsTo(Order::class);
  }
}
