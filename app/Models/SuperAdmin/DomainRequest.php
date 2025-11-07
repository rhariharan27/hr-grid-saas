<?php

namespace App\Models\SuperAdmin;

use App\Models\User;
use App\Traits\TenantTrait;
use OwenIt\Auditing\Auditable;
use App\Traits\UserActionsTrait;
use App\Enums\DomainRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DomainRequest extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'domain_requests';

  protected $fillable = [
    'user_id',
    'name',
    'data',
    'status',
    'approved_by_id',
    'rejected_by_id',
    'cancelled_by_id',
    'approve_reason',
    'reject_reason',
    'tenant_id',
    'created_by_id',
    'updated_by_id',
  ];

  protected $casts = [
    'status' => DomainRequestStatus::class,
    'approved_at' => 'datetime',
    'rejected_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
