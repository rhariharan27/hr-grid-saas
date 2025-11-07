<?php

namespace App\Traits;

use App\Models\Tenant;

trait TenantTrait
{
  public function tenant()
  {
    return $this->belongsTo(Tenant::class);
  }


  protected static function bootTenantTrait(): void
  {
    //Set tenant_id on creation
    static::creating(function ($model) {
      //if tenant_id is not set, set it to the current tenant id
      if (empty($model->tenant_id) && tenancy()->initialized) {
        $model->tenant_id = tenant()->id;
      } else if (empty($model->tenant_id)) {
        //if tenant_id is not set and tenancy is not initialized, set it to null
        $model->tenant_id = '';
      }
    });
  }
}
