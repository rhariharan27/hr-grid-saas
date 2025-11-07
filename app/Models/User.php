<?php

namespace App\Models;

use App\Enums\OfflineRequestStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserAccountStatus;
use App\Models\SuperAdmin\OfflineRequest;
use App\Models\SuperAdmin\Plan;
use App\Models\SuperAdmin\Subscription;
use App\Traits\UserActionsTrait;
use App\Traits\UserTenantOptionsTrait;
use Carbon\Carbon;
use Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, AuditableContract
{
  use UserTenantOptionsTrait, HasFactory, HasApiTokens, Notifiable, HasRoles, Auditable, UserActionsTrait, SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'phone',
    'status',
    'dob',
    'gender',
    'profile_picture',
    'alternate_number',
    'cover_picture',
    'email',
    'email_verified_at',
    'phone_verified_at',
    'password',
    'remember_token',
    'language',
    'delete_request_at',
    'designation_id',
    'shift_id',
    'delete_request_reason',
    'team_id',
    'code',
    'date_of_joining',
    'base_salary',
    'anniversary_date',
    'available_leave_count',
    'relieved_at',
    'relieved_reason',
    'retired_at',
    'retired_reason',
    'is_customer',
    'exit_date',
    'exit_reason',
    'termination_type',
    'last_working_day',
    'is_eligible_for_rehire',
    'notice_period_days',
    'probation_period_months',
    'probation_end_date',
    'probation_confirmed_at',
    'is_probation_extended',
    'probation_remarks',
    'passport_number',      // Add this line
    'passport_expiry_date', // Add this line
    'visa_number',          // Add this line
    'visa_expiry_date',     // Add this line
  ];
  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  public function getUserForProfile()
  {
    return [
      'name' => $this->getFullName(),
      'code' => $this->code,
      'initials' => $this->getInitials(),
      'profile_picture' => $this->getProfilePicture(),
    ];
  }

  /**
   * Check if the user is currently under probation.
   */
  public function isUnderProbation(): bool
  {
    return $this->status === UserAccountStatus::ACTIVE && // Must be active
      !is_null($this->probation_end_date) && // Must have an end date
      is_null($this->probation_confirmed_at) && // Must not be confirmed yet
      Carbon::parse($this->probation_end_date)->isFuture(); // End date must be in the future
  }

  /**
   * Get a display string for the user's probation status.
   */
  public function getProbationStatusDisplayAttribute(): string
  {
    if ($this->status !== UserAccountStatus::ACTIVE || is_null($this->probation_end_date)) {
      return 'Not Applicable';
    }
    if (!is_null($this->probation_confirmed_at)) {
      return 'Completed on ' . Carbon::parse($this->probation_confirmed_at)->format('M d, Y');
    }
    if ($this->isUnderProbation()) {
      $statusText = 'Active until ' . Carbon::parse($this->probation_end_date)->format('M d, Y');
      if ($this->is_probation_extended) {
        $statusText .= ' (Extended)';
      }
      return $statusText;
    }
    if (Carbon::parse($this->probation_end_date)->isPast()) {
      // Past due date but not confirmed - needs action
      return 'Pending Confirmation (Ended ' . Carbon::parse($this->probation_end_date)->format('M d, Y') . ')';
    }
    return 'Unknown'; // Should not happen often
  }
  // --- End Probation Accessors ---


  public function getFullName()
  {
    return $this->first_name . ' ' . $this->last_name;
  }

  public function getInitials(): string
  {
    return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
  }

  public function getProfilePicture()
  {
    if (is_null($this->profile_picture)) {
      return null;
    } else {
      if (tenancy()->initialized) {
        return tenant_asset(Constants::BaseFolderEmployeeProfileWithSlash . $this->profile_picture);
      } else {
        return asset('/storage/' . Constants::BaseFolderEmployeeProfileWithSlash . $this->profile_picture);
      }
    }
  }

  public function activePlan()
  {
    return $this->belongsTo(Plan::class, 'plan_id');
  }

  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims()
  {
    return [];
  }

  /**
   * Specifies the user's FCM tokens
   *
   * @return string|array
   */
  public function fcmToken()
  {
    return $this->getDeviceToken();
  }

  public function getDeviceToken()
  {
    $userDevice = UserDevice::where('user_id', $this->id)->first();
    return $userDevice?->token;
  }

  public function hasActivePlan(): bool
  {
    return $this->plan_id != null && $this->plan_expired_date >= now()->toDateString();
  }

  public function hasPendingOfflineRequest(): bool
  {
    return OfflineRequest::where('user_id', $this->id)
      ->where('status', OfflineRequestStatus::PENDING)
      ->exists();
  }

  public function activeSubscription()
  {
    return $this->subscriptions()
      ->where('status', SubscriptionStatus::ACTIVE)
      ->where('end_date', '>=', now()->toDateString())
      ->first();
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }


  //Tenant Specific

  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'phone_verified_at' => 'datetime',
      'dob' => 'date',
      'probation_end_date' => 'date',
      'status' => UserAccountStatus::class,
    ];
  }
}
