<?php

namespace App\Models;

use App\Traits\TenantTrait;
use App\Traits\UserActionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ExpenseRequest extends Model implements AuditableContract
{
  use Auditable, UserActionsTrait, TenantTrait, SoftDeletes;

  protected $table = 'expense_requests';

  protected $fillable = [
    'user_id',
    'expense_date',
    'user_notes',
    'total_amount',
    'total_items',
    'total_approved_amount',
    'pending_amount',
    'status',
    'approval_notes',
    'notes',
    'approved_by_id',
    'rejected_by_id',
    'approved_at',
    'rejected_at',
    'processed_at',
    'created_by_id',
    'updated_by_id',
    'tenant_id',
  ];

  protected $casts = [
    'for_date' => 'date:d-m-Y',
    'approved_at' => 'datetime',
    'created_at' => 'datetime',
    'rejected_at' => 'datetime',
    'processed_at' => 'datetime',
    'total_amount' => 'float',
    'total_approved_amount' => 'float',
    'pending_amount' => 'float',

  ];

  public function expenseType()
  {
    return $this->belongsTo(ExpenseType::class, 'expense_type_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function approvedBy()
  {
    return $this->belongsTo(User::class, 'approved_by_id');
  }

  public function rejectedBy()
  {
    return $this->belongsTo(User::class, 'rejected_by_id');
  }

  public function items()
  {
    return $this->hasMany(ExpenseRequestItem::class);
  }
}
