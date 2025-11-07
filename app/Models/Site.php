<?php

namespace App\Models;

use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Site extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'sites';

  protected $fillable = [
    'name',
    'description',
    'latitude',
    'longitude',
    'radius',
    'address',
    'status',
    'is_attendance_enabled',
    'attendance_type',
    'client_id',
    'shift_id',
    'created_by_id',
    'updated_by_id',
    'tenant_id',
    'geofence_group_id',
    'ip_address_group_id',
    'qr_group_id',
  ];

  protected $casts = [
    'is_attendance_enabled' => 'boolean',
    'latitude' => 'float',
    'longitude' => 'float',
    'radius' => 'float',
  ];

  public function client()
  {
    return $this->belongsTo(Client::class, 'client_id');
  }

  public function shift()
  {
    return $this->belongsTo(Shift::class, 'shift_id');
  }

  public function geofenceGroup()
  {
    return $this->belongsTo(GeofenceGroup::class, 'geofence_group_id');
  }

  public function ipAddressGroup()
  {
    return $this->belongsTo(IpAddressGroup::class, 'ip_address_group_id');
  }

  public function qrGroup()
  {
    return $this->belongsTo(QrGroup::class, 'qr_group_id');
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'site_users', 'site_id', 'user_id');
  }
}
