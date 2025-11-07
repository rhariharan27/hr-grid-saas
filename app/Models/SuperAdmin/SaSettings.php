<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class SaSettings extends Model implements AuditableContract
{
  use Auditable;

  public $timestamps = false;

  protected $table = 'sa_settings';

  protected $fillable = [
    'app_version',
    'app_force_update',
    'currency',
    'currency_symbol',
    'currency_position',
    'privacy_policy_url',
    'paypal_enabled',
    'paypal_mode',
    'paypal_client_id',
    'paypal_secret',
    'razorpay_enabled',
    'razorpay_key',
    'razorpay_secret',
    'offline_payment_enabled',
    'offline_payment_instructions',
    'use_per_tenant_map_key',
    'support_email',
    'support_phone',
    'support_whatsapp',
    'website',
    'google_recaptcha_site_key',
    'google_recaptcha_secret_key',
    'is_google_recaptcha_enabled',
  ];


  protected $casts = [
    'app_force_update' => 'boolean',
    'paypal_enabled' => 'boolean',
    'razorpay_enabled' => 'boolean',
    'offline_payment_enabled' => 'boolean',
    'use_per_tenant_map_key' => 'boolean',
    'is_google_recaptcha_enabled' => 'boolean',
  ];

  protected static function boot()
  {
    parent::boot();
    static::saved(function () {
      Cache::forget('sa_app_settings');
    });
  }
}
