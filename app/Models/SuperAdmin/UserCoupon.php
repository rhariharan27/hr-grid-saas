<?php

namespace App\Models\SuperAdmin;

use App\Models\User;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCoupon extends Model
{
  use UserActionsTrait, SoftDeletes;

  protected $table = 'user_coupons';

  protected $fillable = [
    'user_id',
    'coupon_id',
    'order_id',
  ];

  public function coupon()
  {
    return $this->belongsTo(Coupon::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function order()
  {
    return $this->belongsTo(Order::class);
  }
}
