<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
  protected $fillable = [
    'name',
    'guard_name',
    'is_location_activity_tracking_enabled',
    'is_mobile_app_access_enabled',
    'is_multiple_check_in_enabled'
  ];

  protected $casts = [
    'is_location_activity_tracking_enabled' => 'boolean',
    'is_mobile_app_access_enabled' => 'boolean',
    'is_multiple_check_in_enabled' => 'boolean'
  ];
}
