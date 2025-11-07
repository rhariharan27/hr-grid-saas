<?php

namespace App\Http\Controllers\tenant;

use Constants;
use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use App\Enums\Gender;
use App\Enums\Status;
use App\Models\Shift;
use App\Models\QrGroup;
use App\Models\Settings;
use App\ApiClasses\Error;
use App\Enums\TargetType;
use App\Models\LeaveType;
use App\Models\UserDevice;
use App\ApiClasses\Success;
use App\Enums\CommonStatus;
use App\Models\BankAccount;
use App\Models\Designation;
use App\Models\SalesTarget;
use App\Enums\IncentiveType;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\GeofenceGroup;
use App\Enums\TerminationType;
use App\Models\IpAddressGroup;
use App\Models\DynamicQrDevice;
use Illuminate\Validation\Rule;
use App\Enums\UserAccountStatus;
use Illuminate\Http\JsonResponse;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\Payroll\app\Models\PayrollAdjustment;


class EmployeeController extends Controller
{

  public function addOrUpdatePayrollAdjustment(Request $request)
  {
    $validated = $request->validate([
      'id' => 'nullable|exists:payroll_adjustments,id',
      'adjustmentName' => 'required|string|max:255',
      'adjustmentCode' => 'required|string|max:191',
      'adjustmentType' => 'required|in:benefit,deduction',
      'adjustmentAmount' => 'nullable|numeric|min:0',
      'adjustmentPercentage' => 'nullable|numeric|min:0|max:100',
      'adjustmentNotes' => 'nullable|string|max:1000',
    ]);

    try {
      PayrollAdjustment::updateOrCreate(
        ['id' => $validated['id']],
        [
          'user_id' => $request->userId,
          'name' => $validated['adjustmentName'],
          'code' => $validated['adjustmentCode'],
          'type' => $validated['adjustmentType'],
          'applicability' => 'employee',
          'amount' => $validated['adjustmentAmount'] ?? 0,
          'percentage' => $validated['adjustmentPercentage'],
          'notes' => $validated['adjustmentNotes'],
          'updated_by_id' => auth()->id(),
        ]
      );

      return redirect()->back()->with('success', __('Payroll adjustment saved successfully.'));
    } catch (Exception $e) {
      Log::error('Payroll Adjustment Error: ' . $e->getMessage());
      return redirect()->back()->with('error', __('Failed to save payroll adjustment.'));
    }
  }

  public function getPayrollAdjustmentAjax($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:payroll_adjustments,id'])->validate();

    $payrollAdjustment = PayrollAdjustment::find($validated['id']);

    return Success::response($payrollAdjustment);
  }

  public function addOrUpdateBankAccount(Request $request)
  {
    $validated = $request->validate([
      'userId' => 'required|exists:users,id',
      'bankName' => 'required|string|max:255',
      'bankCode' => 'required|string|max:255',
      'accountName' => 'required|string|max:255',
      'accountNumber' => 'required|string|max:255',
      'branchName' => 'required|string|max:255',
      'branchCode' => 'required|string|max:255'
    ]);

    $user = User::find($validated['userId']);

    $bank = BankAccount::where('user_id', $user->id)
      ->first();

    if ($bank) {
      $bank->bank_name = $validated['bankName'];
      $bank->bank_code = $validated['bankCode'];
      $bank->account_name = $validated['accountName'];
      $bank->account_number = $validated['accountNumber'];
      $bank->branch_name = $validated['branchName'];
      $bank->branch_code = $validated['branchCode'];
      $bank->save();
    } else {
      $user->bankAccount()->create([
        'bank_name' => $validated['bankName'],
        'bank_code' => $validated['bankCode'],
        'account_name' => $validated['accountName'],
        'account_number' => $validated['accountNumber'],
        'branch_name' => $validated['branchName'],
        'branch_code' => $validated['branchCode']
      ]);
    }

    return redirect()->back()->with('success', 'Bank account added/updated successfully');
  }

  public function create()
  {

    if (User::count() >= Settings::first()->employees_limit) {
      return redirect()->back()->with('error', 'You have reached the maximum limit of employees');
    }

    $shifts = Shift::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $teams = Team::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $designations = Designation::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $users = User::where('status', UserAccountStatus::ACTIVE)
      ->select('id', 'first_name', 'last_name', 'code')
      ->get();

    $roles = Role::get();

    return view('tenant.employees.create', [
      'shifts' => $shifts,
      'teams' => $teams,
      'designations' => $designations,
      'users' => $users,
      'roles' => $roles,
    ]);
  }

  public function deletePayrollAdjustment($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:payroll_adjustments,id'])->validate();

    $payrollAdjustment = PayrollAdjustment::find($validated['id']);

    if ($payrollAdjustment) {
      $payrollAdjustment->delete();
    }

    return redirect()->back()->with('success', 'Payroll adjustment deleted successfully');
  }

  public function addOrUpdateSalesTarget(Request $request)
  {

    $validated = $request->validate([
      'targetId' => 'nullable|exists:sales_targets,id',
      'userId' => 'required|exists:users,id',
      'period' => 'required|numeric',
      'targetType' => ['required', Rule::in(array_column(TargetType::cases(), 'value'))],
      'incentiveType' => ['required', Rule::in(array_column(IncentiveType::cases(), 'value'))],
      'targetAmount' => 'required|numeric',
      'incentiveAmount' => 'nullable|numeric|required_if:incentiveType,flat',
      'incentivePercentage' => 'nullable|numeric|required_if:incentiveType,percentage',
      'description' => 'nullable|string|max:255',
    ]);

    $user = User::find($validated['userId']);

    $salesTarget = $user->salesTargets()
      ->where('id', $validated['targetId'])
      ->first();

    if ($salesTarget) {
      $salesTarget->target_amount = $validated['targetAmount'];
      $salesTarget->target_type = TargetType::from($validated['targetType']);
      $salesTarget->period = $validated['period'];
      $salesTarget->description = $validated['description'];
      $salesTarget->incentive_type = IncentiveType::from($validated['incentiveType']);
      $salesTarget->incentive_amount = $validated['incentiveAmount'] ?? 0;
      $salesTarget->incentive_percentage = $validated['incentivePercentage'] ?? 0;
      $salesTarget->save();
    } else {

      if ($user->salesTargets()->where('period', $validated['period'])->exists() && $user->salesTargets()->where('target_type', TargetType::from($validated['targetType']))->exists()) {
        return redirect()->back()->with('error', 'Sales target already exists for this period and target type');
      }

      $user->salesTargets()->create([
        'period' => $validated['period'],
        'target_type' => TargetType::from($validated['targetType']),
        'target_amount' => $validated['targetAmount'],
        'incentive_type' => IncentiveType::from($validated['incentiveType']),
        'incentive_amount' => $validated['incentiveAmount'] ?? 0,
        'incentive_percentage' => $validated['incentivePercentage'] ?? 0,
        'description' => $validated['description']
      ]);
    }

    return redirect()->back()->with('success', 'Sales target added/updated successfully');
  }

  public function destroySalesTarget($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:sales_targets,id'])->validate();

    $salesTarget = SalesTarget::find($validated['id']);

    if ($salesTarget) {
      $salesTarget->delete();
    }

    return redirect()->back()->with('success', 'Sales target deleted successfully');
  }

  public function getTargetByIdAjax($id)
  {
    $validated = validator(['id' => $id], ['id' => 'required|exists:sales_targets,id'])->validate();

    $salesTarget = SalesTarget::find($validated['id']);

    return Success::response($salesTarget);
  }

  public function removeDevice(Request $request)
  {
    $validated = $request->validate([
      'userId' => 'required|exists:users,id',
    ]);

    $device = UserDevice::where('user_id', $validated['userId'])
      ->first();

    if ($device) {
      $device->delete();
    }

    return redirect()->back()->with('success', 'Device removed successfully');
  }

  public function getReportingToUsersAjax()
  {
    $users = User::where('status', UserAccountStatus::ACTIVE)
      ->select('id', 'first_name', 'last_name', 'code')
      ->get();

    return Success::response($users);
  }

  public function updateWorkInformation(Request $request)
  {

    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'doj' => 'required|date',
      'teamId' => 'required|exists:teams,id',
      'shiftId' => 'required|exists:shifts,id',
      'designationId' => 'required|exists:designations,id',
      'role' => 'required|exists:roles,name',
      'reportingToId' => 'required|exists:users,id',
      'attendanceType' => 'required|in:open,geofence,ipAddress,staticqr,site,dynamicqr,face',
      'geofenceGroupId' => 'required_if:attendanceType,geofence|exists:geofence_groups,id',
      'ipGroupId' => 'required_if:attendanceType,ipAddress|exists:ip_address_groups,id',
      'qrGroupId' => 'required_if:attendanceType,staticqr|exists:qr_groups,id',
      'siteId' => 'required_if:attendanceType,site|exists:sites,id',
      'dynamicQrId' => 'required_if:attendanceType,dynamicqr|exists:dynamic_qr_devices,id',
    ]);

    $user = User::find($validated['id']);

    if ($user->date_of_joining != $validated['doj']) {
      $user->date_of_joining = $validated['doj'];
    }

    if ($user->team_id != $validated['teamId']) {
      $user->team_id = $validated['teamId'];
    }

    if ($user->shift_id != $validated['shiftId']) {
      $user->shift_id = $validated['shiftId'];
    }

    if ($user->designation_id != $validated['designationId']) {
      $user->designation_id = $validated['designationId'];
    }

    if ($user->reporting_to_id != $validated['reportingToId']) {
      $user->reporting_to_id = $validated['reportingToId'];
    }

    switch ($validated['attendanceType']) {
      case 'geofence':
        $user->attendance_type = 'geofence';
        $user->geofence_group_id = $validated['geofenceGroupId'];
        break;
      case 'ipAddress':
        $user->attendance_type = 'ip_address';
        $user->ip_address_group_id = $validated['ipGroupId'];
        break;
      case 'staticqr':
        $user->attendance_type = 'qr_code';
        $user->qr_group_id = $validated['qrGroupId'];
        break;
      case 'site':
        $user->attendance_type = 'site';
        $user->site_id = $validated['siteId'];
        break;
      case 'dynamicqr':
        $user->attendance_type = 'dynamic_qr';
        $user->dynamic_qr_device_id = $validated['dynamicQrId'];
        DynamicQrDevice::where('id', $validated['dynamicQrId'])
          ->update(['user_id' => $user->id, 'status' => 'in_use']);
        break;
      case 'face':
        $user->attendance_type = 'face_recognition';
        break;
      default:
        $user->attendance_type = 'open';
        break;
    }


    $user->save();

    // Update user role
    $role = Role::where('name', $validated['role'])->first();
    $user->roles()->sync($role->id);

    return redirect()->back()->with('success', 'Work information updated successfully');
  }

  public function updateCompensationInfo(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'baseSalary' => 'nullable|numeric',
      'availableLeaveCount' => 'nullable|numeric',
    ]);

    $user = User::find($validated['id']);

    if ($user->base_salary != $validated['baseSalary']) {
      $user->base_salary = $validated['baseSalary'];
    }

    if ($user->available_leave_count != $validated['availableLeaveCount']) {
      $user->available_leave_count = $validated['availableLeaveCount'];
    }

    $user->save();

    return redirect()->back()->with('success', 'Compensation info updated successfully');
  }

  public function updateBasicInfo(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|exists:users,id',
      'firstName' => 'required|string|max:255',
      'lastName' => 'required|string',
      'dob' => 'required|date',
      'gender' => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
      'phone' => 'required|string|min:1|max:15',
      'altPhone' => 'nullable|string|max:15',
      'email' => 'required|email',
      'address' => 'nullable|string|max:255',
      'passport_number' => 'nullable|string|max:255',
      'passport_expiry_date' => 'nullable|date',
      'visa_number' => 'nullable|string|max:255',
      'visa_expiry_date' => 'nullable|date',
    ]);

    $user = User::find($validated['id']);

    if ($user->first_name != $validated['firstName']) {
      $user->first_name = $validated['firstName'];
    }

    if ($user->last_name != $validated['lastName']) {
      $user->last_name = $validated['lastName'];
    }

    if ($user->dob != $validated['dob']) {
      $user->dob = $validated['dob'];
    }

    if ($user->gender != $validated['gender']) {
      $user->gender = Gender::from($validated['gender']);
    }

    if ($user->phone != $validated['phone']) {
      $user->phone = $validated['phone'];
    }

    if ($user->alternate_number != $validated['altPhone']) {
      $user->alternate_number = $validated['altPhone'];
    }

    if ($user->email != $validated['email']) {
      $user->email = $validated['email'];
    }

    if ($user->address != $validated['address']) {
      $user->address = $validated['address'];
    }

    if ($user->passport_number != $validated['passport_number']) {
      $user->passport_number = $validated['passport_number'];
    }
    if ($user->passport_expiry_date != $validated['passport_expiry_date']) {
      $user->passport_expiry_date = $validated['passport_expiry_date'];
    }
    if ($user->visa_number != $validated['visa_number']) {
      $user->visa_number = $validated['visa_number'];
    }
    if ($user->visa_expiry_date != $validated['visa_expiry_date']) {
      $user->visa_expiry_date = $validated['visa_expiry_date'];
    }

    $user->save();

    return redirect()->back()->with('success', 'Basic info updated successfully');
  }

  /**
   * Initiate the termination process for an employee.
   */
  public function initiateTermination(Request $request, User $user)
  {
    /*// --- Authorization & Pre-condition Check ---
    if (!Auth::user()->can('terminate employees')) { // Example permission
      return Error::response('Permission denied.', 403);
    }*/
    if ($user->status == UserAccountStatus::TERMINATED) { // Use Enum comparison
      return Error::response('Employee is already terminated.', 409);
    }

    // --- Validation ---
    $validator = Validator::make($request->all(), [
      'exitDate' => 'required|date_format:Y-m-d',
      'lastWorkingDay' => 'required|date_format:Y-m-d|after_or_equal:exitDate',
      'terminationType' => ['required', new Enum(TerminationType::class)], // Use Enum validation if created
      // 'terminationType' => 'required|string|in:resignation,terminated_with_cause,...', // Alternative if not using Enum model cast
      'exitReason' => 'required|string|max:1000',
      'isEligibleForRehire' => 'required|boolean',
    ]);

    if ($validator->fails()) {
      return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
    }

    // --- Update User ---
    DB::beginTransaction();
    try {
      $validatedData = $validator->validated();
      $adminUserId = Auth::id();

      $user->update([
        'status' => UserAccountStatus::TERMINATED, // Set status
        'exit_date' => $validatedData['exitDate'],
        'last_working_day' => $validatedData['lastWorkingDay'],
        'termination_type' => $validatedData['terminationType'],
        'exit_reason' => $validatedData['exitReason'],
        'is_eligible_for_rehire' => filter_var($validatedData['isEligibleForRehire'], FILTER_VALIDATE_BOOLEAN),
        'updated_by_id' => $adminUserId,
        // Maybe clear tokens, disable login? Depends on setup.
      ]);

      // TODO: Trigger Offboarding Checklist / Notifications?

      // Log this action (using a generic activity logger or specific audit)
      Log::info("User ID {$user->id} terminated by User ID {$adminUserId}. Reason: {$validatedData['exitReason']}");
      // Or use JobApplicationActivity if termination stems from recruitment flow? Less likely here.

      DB::commit();

      // Return structure consistent with your Success::response wrapper if applicable
      return response()->json([
        'success' => true,
        'message' => 'Employee termination process initiated successfully.'
      ]);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error terminating employee ID {$user->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'An error occurred during termination.'], 500);
    }
  }


  /**
   * Confirm the successful completion of an employee's probation.
   *
   * @param Request $request
   * @param User $user The employee whose probation is being confirmed (Route Model Binding)
   *
   */
  public function confirmProbation(Request $request, User $user)
  {
    /* // --- Authorization Check ---
     // Example: Replace with your actual permission check
     if (!Auth::user()->can('manage_probation')) {
       return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
     }*/

    // --- Pre-condition Check ---
    // Check if user exists and is actually eligible for probation confirmation
    // (e.g., has a probation end date, isn't already confirmed, isn't terminated)
    // Using the accessor assumes it checks for null end_date and null confirmed_at
    // Add more checks if needed based on your exact logic for eligibility
    if ($user->probation_confirmed_at !== null) {
      return response()->json(['success' => false, 'message' => 'Probation has already been confirmed for this employee.'], 409); // 409 Conflict
    }
    if (is_null($user->probation_end_date)) {
      return response()->json(['success' => false, 'message' => 'This employee does not have a probation period defined.'], 400);
    }
    // Optional: Check if probation period actually ended? Or allow early confirmation?
    // if (Carbon::parse($user->probation_end_date)->isFuture()) {
    //    return response()->json(['success' => false, 'message' => 'Probation period has not ended yet.'], 400);
    // }


    // --- Validation ---
    $validator = Validator::make($request->all(), [
      'probationRemarks' => 'nullable|string|max:2000', // Optional remarks
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed.',
        'errors' => $validator->errors()
      ], 422);
    }

    // --- Update User ---
    DB::beginTransaction(); // Optional: Use transaction if other actions occur
    try {
      $adminUser = Auth::user();
      $remarks = $request->input('probationRemarks');
      $confirmationTimestamp = now();

      // Construct remarks entry
      $remarkEntry = "Probation confirmed by {$adminUser->getFullName()} on " . $confirmationTimestamp->format('Y-m-d H:i') . ".";
      if (!empty($remarks)) {
        $remarkEntry .= "\nRemarks: " . $remarks;
      }

      $user->probation_confirmed_at = $confirmationTimestamp;
      // Append remarks or set them - decide on your preferred logic
      $user->probation_remarks = ($user->probation_remarks ? $user->probation_remarks . "\n\n---\n\n" : '') . $remarkEntry;
      // Optional: Update user status if needed, though likely already ACTIVE
      // $user->status = UserAccountStatus::ACTIVE;
      $user->save();

      // TODO: Log this action (e.g., Audit log or specific activity log)
      Log::info("Probation confirmed for User ID {$user->id} by Admin ID {$adminUser->id}.");

      DB::commit(); // Commit transaction if used

      // Return success response consistent with your standard
      return response()->json([
        'success' => true,
        'message' => 'Employee probation confirmed successfully.'
        // Optionally return updated user data or probation status
      ]);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      Log::error("Error confirming probation for User ID {$user->id}: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'An error occurred while confirming probation.'
      ], 500);
    }
  }

  /**
   * Extend the probation period for an employee.
   *
   * @param Request $request
   * @param User $user The employee whose probation is being extended
   * @return JsonResponse
   */
  public function extendProbation(Request $request, User $user): JsonResponse
  {
    /* // --- Authorization Check ---
     if (!Auth::user()->can('manage_probation')) { // Example permission
       return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
     }*/

    // --- Pre-condition Check ---
    if ($user->probation_confirmed_at !== null) {
      return response()->json(['success' => false, 'message' => 'Probation has already been confirmed.'], 409);
    }
    if (is_null($user->probation_end_date)) {
      return response()->json(['success' => false, 'message' => 'No probation period defined for extension.'], 400);
    }
    if ($user->status !== UserAccountStatus::ACTIVE) {
      return response()->json(['success' => false, 'message' => 'Employee must be active to extend probation.'], 400);
    }

    // --- Validation ---
    $currentEndDate = Carbon::parse($user->probation_end_date);
    $validator = Validator::make($request->all(), [
      // New end date must be after the current probation end date
      'newProbationEndDate' => ['required', 'date_format:Y-m-d', 'after:' . $currentEndDate->toDateString()],
      'probationRemarks' => 'required|string|max:2000', // Reason for extension is required
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed.',
        'errors' => $validator->errors()
      ], 422);
    }

    // --- Update User ---
    DB::beginTransaction();
    try {
      $adminUser = Auth::user();
      $validatedData = $validator->validated();
      $newEndDate = $validatedData['newProbationEndDate'];
      $reason = $validatedData['probationRemarks'];
      $extensionTimestamp = now();

      // Construct remark entry for extension
      $remarkEntry = "Probation extended by {$adminUser->getFullName()} on " . $extensionTimestamp->format('Y-m-d H:i') . " to {$newEndDate}.";
      $remarkEntry .= "\nReason: " . $reason;

      $user->probation_end_date = $newEndDate;
      $user->is_probation_extended = true; // Mark as extended
      $user->probation_remarks = ($user->probation_remarks ? $user->probation_remarks . "\n\n---\n\n" : '') . $remarkEntry;
      // Ensure confirmation date is null if extending
      $user->probation_confirmed_at = null;
      $user->save();

      // TODO: Log this action
      Log::info("Probation extended for User ID {$user->id} to {$newEndDate} by Admin ID {$adminUser->id}.");

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Employee probation extended successfully.'
      ]);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error extending probation for User ID {$user->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'An error occurred while extending probation.'], 500);
    }
  }


  /**
   * Fail the probation period for an employee, initiating termination.
   *
   * @param Request $request
   * @param User $user The employee failing probation
   * @return JsonResponse
   */
  public function failProbation(Request $request, User $user): JsonResponse
  {
    // --- Authorization Check ---
    // Failing probation often leads to termination, might require termination permission
    /* if (!Auth::user()->can('manage_probation') || !Auth::user()->can('terminate_employees')) {
       return response()->json(['success' => false, 'message' => 'Permission denied.'], 403);
     }*/

    // --- Pre-condition Check ---
    if ($user->probation_confirmed_at !== null) {
      return response()->json(['success' => false, 'message' => 'Probation has already been confirmed.'], 409);
    }
    if ($user->status !== UserAccountStatus::ACTIVE) { // Must be active to fail probation (not already terminated etc.)
      return response()->json(['success' => false, 'message' => 'Employee is not currently active.'], 400);
    }
    if (is_null($user->probation_end_date)) {
      return response()->json(['success' => false, 'message' => 'No probation period defined to fail.'], 400);
    }


    // --- Validation ---
    $validator = Validator::make($request->all(), [
      'probationRemarks' => 'required|string|max:2000', // Reason for failure is required
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed.',
        'errors' => $validator->errors()
      ], 422);
    }

    // --- Update User (Terminate due to Probation Failure) ---
    DB::beginTransaction();
    try {
      $adminUser = Auth::user();
      $validatedData = $validator->validated();
      $reason = $validatedData['probationRemarks'];
      $terminationTimestamp = now();

      // Construct remark entry for failure
      $remarkEntry = "Probation failed by {$adminUser->getFullName()} on " . $terminationTimestamp->format('Y-m-d H:i') . ".";
      $remarkEntry .= "\nReason: " . $reason;

      // Update user record to reflect termination due to probation failure
      $user->status = UserAccountStatus::TERMINATED; // Or UserAccountStatus::PROBATION_FAILED if using specific status
      $user->exit_date = $terminationTimestamp->toDateString();
      $user->last_working_day = $terminationTimestamp->toDateString(); // Or set differently if needed
      $user->termination_type = TerminationType::PROBATION_FAILED->value; // Use Enum
      $user->exit_reason = "Probation Failed: " . $reason;
      $user->is_eligible_for_rehire = false; // Typically not eligible after probation failure
      $user->probation_remarks = ($user->probation_remarks ? $user->probation_remarks . "\n\n---\n\n" : '') . $remarkEntry;
      // Ensure confirmation date is null
      $user->probation_confirmed_at = null;
      $user->updated_by_id = $adminUser->id;
      $user->save();

      // TODO: Log this action (Termination + Probation Failure)
      Log::info("Probation failed for User ID {$user->id}. Terminated by Admin ID {$adminUser->id}. Reason: {$reason}");

      // TODO: Trigger Offboarding / Notifications?

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Employee probation failed and termination process initiated.'
      ]);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error failing probation for User ID {$user->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'An error occurred while failing probation.'], 500);
    }
  }


  public function index()
  {
    $active = User::where('status', UserAccountStatus::ACTIVE)->count();
    $inactive = User::where('status', UserAccountStatus::INACTIVE)->count();
    $relieved = User::where('status', UserAccountStatus::RELIEVED)->count();

    $roles = Role::select('id', 'name')
      ->get();

    $teams = Team::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    $designations = Designation::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    return view('tenant.employees.index', [
      'totalUser' => $active + $inactive + $relieved,
      'active' => $active,
      'inactive' => $inactive,
      'relieved' => $relieved,
      'roles' => $roles,
      'teams' => $teams,
      'designations' => $designations,
    ]);
  }

  public function changeEmployeeProfilePicture(Request $request)
  {
    $rules = [
      'userId' => 'required|exists:users,id',
      'file' => 'required|image|mimes:jpeg,png,jpg|max:5096',
    ];

    $validatedData = $request->validate($rules);

    try {
      $user = User::find($request->input('userId'));

      if (!$user) {
        return Error::response('User not found');
      }

      if ($request->hasFile('file')) {
        $file = $request->file('file');
        $fileName = $user->code . '_' . time() . '.' . $file->getClientOriginalExtension();

        //Delete Old File
        $oldProfilePicture = $user->profile_picture;
        if (!is_null($oldProfilePicture)) {
          $oldProfilePicturePath = Storage::disk('public')->path(Constants::BaseFolderEmployeeProfileWithSlash . $oldProfilePicture);
          if (file_exists($oldProfilePicturePath)) {
            Storage::delete($oldProfilePicturePath);
          }
        }

        //Create Directory if not exists
        if (!Storage::disk('public')->exists(Constants::BaseFolderEmployeeProfile)) {
          Storage::disk('public')->makeDirectory(Constants::BaseFolderEmployeeProfile);
        }

        Storage::disk('public')->putFileAs(Constants::BaseFolderEmployeeProfileWithSlash, $file, $fileName);

        $user->profile_picture = $fileName;
        $user->save();
      }

      return redirect()->back()->with('success', 'Profile picture updated successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@changeEmployeeProfilePicture: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to update profile picture');
    }
  }

  public function getListAjax(Request $request)
  {

    $settings = Settings::first();

    $query = User::query()
      ->with('roles', 'team', 'designation')
      ->select('users.*');

    // Apply filters if set in the request
    if ($request->filled('roleFilter')) {
      $query->whereHas('roles', function ($q) use ($request) {
        $q->where('name', $request->roleFilter);
      });
    }
    if ($request->filled('teamFilter')) {
      $query->where('team_id', $request->teamFilter);
    }
    if ($request->filled('designationFilter')) {
      $query->where('designation_id', $request->designationFilter);
    }

    return DataTables::of($query)
      // Render the user column as an avatar + full name.
      ->addColumn('user', function ($user) {
        return view('_partials._profile-avatar', ['user' => $user])->render();
      })
      // Add a custom filter for the computed 'user' column.
      ->filterColumn('user', function ($query, $keyword) {
        $query->where(function ($q) use ($keyword) {
          $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])
            ->orWhere('code', 'LIKE', "%{$keyword}%");
        });
      })
      ->editColumn('phone', function ($user) use ($settings) {
        return $settings->phone_country_code . ' ' . $user->phone;
      })
      // Add a simple column for role (using the first assigned role)
      ->addColumn('role', function ($user) {
        $role = $user->roles()->first();
        return $role ? $role->name : 'N/A';
      })
      // Render attendance type
      ->addColumn('attendance_type', function ($user) {
        return ucfirst(str_replace('_', ' ', $user->attendance_type));
      })
      // Render team name
      ->addColumn('team', function ($user) {
        return $user->team ? $user->team->name : 'N/A';
      })
      // Format the status column with a badge
      ->editColumn('status', function ($user) {
        $badge = '<span class="badge bg-secondary">' . ucfirst($user->status->value) . '</span>';
        if ($user->status == UserAccountStatus::ACTIVE) {
          $badge = '<span class="badge bg-success">Active</span>';
        } elseif ($user->status == UserAccountStatus::INACTIVE) {
          $badge = '<span class="badge bg-warning">Inactive</span>';
        } elseif ($user->status == UserAccountStatus::RELIEVED) {
          $badge = '<span class="badge bg-danger">Relieved</span>';
        }
        return $badge;
      })
      // Create an actions column (for view, edit, delete)
      ->addColumn('actions', function ($user) {
        $viewUrl = route('employees.show', $user->id);
        return '<div class="d-flex gap-2">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                    </div>';
      })
      ->rawColumns(['user', 'status', 'actions'])
      ->make(true);
  }

  public function deleteEmployeeAjax($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    try {
      $user = User::find($id);

      if (!$user) {
        return Error::response('User not found');
      }

      $user->delete();

      return Success::response('User deleted successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@deleteEmployeeAjax: ' . $e->getMessage());
      return Error::response('Failed to delete user');
    }
  }

  public function show($id)
  {
    validator(['id' => $id], ['id' => 'required|exists:users,id'])->validate();

    $user = User::where('id', $id)
      ->with('userDevice')
      ->with('team')
      ->with('userAvailableLeaves')
      ->with('shift')
      ->with('designation')
      ->with('salesTargets')
      ->with('bankAccount')
      ->first();

    $documentTypes = DocumentType::where('status', CommonStatus::ACTIVE)
      ->get();

    $leaveTypes = LeaveType::where('status', Status::ACTIVE)
      ->select('id', 'name', 'code')
      ->get();

    return view('tenant.employees.view', [
      'user' => $user,
      'documentTypes' => $documentTypes,
      'leaveTypes' => $leaveTypes,
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'firstName' => 'required|string|max:255',
      'lastName' => 'required|string|max:255',
      'gender' => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
      'phone' => 'required|string|max:15|unique:users,phone',
      'altPhone' => 'nullable|string|max:15',
      'email' => 'required|email|unique:users,email',
      'role' => 'required|exists:roles,name',
      'dob' => 'required|date',
      'address' => 'nullable|string|max:255',
      'file' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
      'useDefaultPassword' => 'nullable',
      'password' => 'nullable|min:6',
      'confirmPassword' => 'nullable|min:6|same:password',

      'code' => 'required|string|max:255|unique:users,code',
      'designationId' => 'required|exists:designations,id',
      'doj' => 'required|date',
      'teamId' => 'required|exists:teams,id',
      'shiftId' => 'required|exists:shifts,id',
      'reportingToId' => 'required|exists:users,id',
      'attendanceType' => 'required|in:open,geofence,ipAddress,staticqr,dynamicqr,site,face',
      'geofenceGroupId' => 'required_if:attendanceType,geofence|exists:geofence_groups,id',
      'ipGroupId' => 'required_if:attendanceType,ipAddress|exists:ip_address_groups,id',
      'qrGroupId' => 'required_if:attendanceType,staticqr|exists:qr_groups,id',
      'siteId' => 'required_if:attendanceType,site|exists:sites,id',
      'dynamicQrId' => 'required_if:attendanceType,dynamicqr|exists:dynamic_qr_devices,id',

      'baseSalary' => 'required|numeric',
      'availableLeaveCount' => 'nullable|numeric',

      'passport_number' => 'nullable|string|max:255',
      'passport_expiry_date' => 'nullable|date',
      'visa_number' => 'nullable|string|max:255',
      'visa_expiry_date' => 'nullable|date',
    ]);

    try {
      $user = new User();
      $user->first_name = $request->input('firstName');
      $user->last_name = $request->input('lastName');
      $user->gender = Gender::from($request->input('gender'));
      $user->phone = $request->input('phone');
      $user->alternate_number = $request->input('altPhone');
      $user->email = $request->input('email');
      $user->dob = $request->input('dob');
      $user->address = $request->input('address');

      $user->passport_number = $request->input('passport_number');
      $user->passport_expiry_date = $request->input('passport_expiry_date');
      $user->visa_number = $request->input('visa_number');
      $user->visa_expiry_date = $request->input('visa_expiry_date');

      if ($request->has('useDefaultPassword') && $request->input('useDefaultPassword') == 'on') {
        $user->password = bcrypt(Settings::first()->default_password ?? 123456);
      } else {
        $user->password = bcrypt($request->input('password'));
      }

      $user->code = $request->input('code');
      $user->date_of_joining = $request->input('doj');
      $user->team_id = $request->input('teamId');
      $user->shift_id = $request->input('shiftId');
      $user->reporting_to_id = $request->input('reportingToId');
      $user->designation_id = $request->input('designationId');
      $user->base_salary = $request->input('baseSalary');

      //Attendance Type Settings
      switch ($request->input('attendanceType')) {
        case 'geofence':
          $user->attendance_type = 'geofence';
          $user->geofence_group_id = $request->input('geofenceGroupId');
          break;
        case 'ipAddress':
          $user->attendance_type = 'ip_address';
          $user->ip_address_group_id = $request->input('ipGroupId');
          break;
        case 'staticqr':
          $user->attendance_type = 'qr_code';
          $user->qr_group_id = $request->input('qrGroupId');
          break;
        case 'site':
          $user->attendance_type = 'site';
          $user->site_id = $request->input('siteId');
          break;
        case 'dynamicqr':
          $user->attendance_type = 'dynamic_qr';
          $user->dynamic_qr_device_id = $request->input('dynamicQrId');
          DynamicQrDevice::where('id', $request->input('dynamicQrId'))
            ->update(['user_id' => $user->id, 'status' => 'in_use']);
          break;
        case 'face':
          $user->attendance_type = 'face_recognition';
          break;
        default:
          $user->attendance_type = 'open';
          break;
      }

      $user->status = UserAccountStatus::ACTIVE;

      if ($request->hasFile('file')) {

        $file = $request->file('file');
        $fileName = $user->code . '_' . time() . '.' . $file->getClientOriginalExtension();

        //Create Directory if not exists
        if (!Storage::disk('public')->exists(Constants::BaseFolderEmployeeProfile)) {
          Storage::disk('public')->makeDirectory(Constants::BaseFolderEmployeeProfile);
        }

        Storage::disk('public')->putFileAs(Constants::BaseFolderEmployeeProfileWithSlash, $file, $fileName);

        $user->profile_picture = $fileName;
      }

      $user->created_by_id = auth()->id();
      $user->save();

      $user->assignRole($request->input('role'));


      return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    } catch (Exception $e) {
      Log::error('EmployeeController@store: ' . $e->getMessage());
      return redirect()->back()->with('error', 'Failed to create employee');
    }
  }

  public function checkEmailValidationAjax(Request $request)
  {
    $email = $request->input('email');

    if (!$email) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit case handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('email', $email)->where('id', '!=', $id)->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('email', $email)->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function checkPhoneValidationAjax(Request $request)
  {

    $phone = $request->input('phone');

    if (!$phone) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit Case Handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('phone', $phone)->where('id', '!=', $id)->withTrashed()->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('phone', $phone)->withTrashed()->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function checkEmployeeCodeValidationAjax(Request $request)
  {
    $code = $request->input('code');

    if (!$code) {
      return response()->json([
        "valid" => false,
      ]);
    }

    //Edit Case Handling
    if ($request->has('id')) {
      $id = $request->input('id');
      if (User::where('code', $code)->where('id', '!=', $id)->withTrashed()->exists()) {
        return response()->json([
          "valid" => false,
        ]);
      } else {
        return response()->json([
          "valid" => true,
        ]);
      }
    }

    if (User::where('code', $code)->withTrashed()->exists()) {
      return response()->json([
        "valid" => false,
      ]);
    }

    return response()->json([
      "valid" => true,
    ]);
  }

  public function getGeofenceGroups()
  {
    $geofenceGroups = GeofenceGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($geofenceGroups);
  }

  public function getIpGroups()
  {
    $ipGroups = IpAddressGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($ipGroups);
  }

  public function getQrGroups()
  {
    $qrGroups = QrGroup::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($qrGroups);
  }

  public function getDynamicQrDevices()
  {
    $devices = DynamicQrDevice::where('user_id', null)
      ->where('site_id', null)
      ->get();

    return response()->json($devices);
  }

  public function getSites()
  {
    $sites = Site::where('status', '=', 'active')
      ->select('id', 'name')
      ->get();

    return response()->json($sites);
  }

  public function toggleStatus($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user->status == UserAccountStatus::ACTIVE) {
      $user->status = UserAccountStatus::INACTIVE;
    } else {
      $user->status = UserAccountStatus::ACTIVE;
    }

    $user->save();

    return Success::response('Status updated successfully');
  }

  public function relieveEmployee($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user) {
      $user->status = UserAccountStatus::RELIEVED;
      $user->relieved_at = now();
      $user->save();
    }

    return Success::response('Employee relieved successfully');
  }

  public function retireEmployee($id)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $user = User::find($id);

    if ($user) {
      $user->status = UserAccountStatus::RETIRED;
      $user->retired_at = now();
      $user->save();
    }

    return Success::response('Employee retired successfully');
  }

  public function myProfile()
  {
    $user = User::find(auth()->user()->id);

    $auditLogs = Audit::where('user_id', auth()->user()->id)
      ->where('auditable_type', 'App\Models\User')
      ->orderBy('created_at', 'desc')
      ->get();

    $role = $user->roles()->first();

    return view('account.my-profile', [
      'user' => $user,
      'auditLogs' => $auditLogs,
      'role' => $role,
    ]);
  }
}
