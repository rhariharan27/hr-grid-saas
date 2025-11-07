<?php

namespace App\Models\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\User;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Order extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'orders';

  protected $fillable = [
    'user_id',
    'plan_id',
    'additional_users',
    'per_user_price',
    'amount',
    'discount_amount',
    'total_amount',
    'status',
    'paid_at',
    'type',
    'payment_id',
    'payment_response',
    'payment_data',
    'payment_gateway',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];

  protected $casts = [
    'per_user_price' => 'float',
    'amount' => 'float',
    'discount' => 'float',
    'total_amount' => 'float',
    'additional_users' => 'integer',
    'status' => OrderStatus::class,
    'type' => OrderType::class,
    'created_at' => 'datetime:Y-m-d H:i A',
    'paid_at' => 'datetime:Y-m-d H:i A',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function offlineRequest()
  {
    return $this->hasOne(OfflineRequest::class);
  }

}
