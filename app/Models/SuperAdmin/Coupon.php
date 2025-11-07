<?php

namespace App\Models\SuperAdmin;

use App\Enums\DiscountType;
use App\Enums\Status;
use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Coupon extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'coupons';

  protected $fillable = [
    'discount_type',
    'code',
    'expiry_date',
    'user_id',
    'discount',
    'limit',
    'description',
    'status',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];

  protected $casts = [
    'expiry_date' => 'date',
    'discount' => 'float',
    'limit' => 'integer',
    'status' => Status::class,
    'discount_type' => DiscountType::class,
  ];

  public function userCoupon()
  {
    return $this->hasMany(UserCoupon::class);
  }
}
