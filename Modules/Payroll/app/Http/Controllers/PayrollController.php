<?php

namespace Modules\Payroll\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Payroll\Enums\PayrollRecordStatus;
use Modules\Payroll\app\Models\PayrollAdjustmentLog;
use Modules\Payroll\app\Models\PayrollCycle;
use Modules\Payroll\app\Models\PayrollRecord;
use Modules\Payroll\app\Models\Payslip;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller
{

  protected $command;

  function __construct(Command $command)
  {
    $this->command = $command;
  }

  public function index()
  {
    $employees = User::all();

    return view('payroll::index', compact('employees'));
  }

  public function getListAjax(Request $request)
  {
    $query = PayrollRecord::with('user')
      ->when($request->has('dateFilter') && !empty($request->dateFilter), function ($query) use ($request) {
        $query->where('period', Carbon::parse($request->dateFilter)->format('F Y'));
      })->when($request->has('employeeFilter') && !empty($request->employeeFilter), function ($query) use ($request) {
        $query->where('user_id', $request->employeeFilter);
      })->when($request->has('statusFilter') && !empty($request->statusFilter), function ($query) use ($request) {
        $query->where('status', $request->statusFilter);
      });

    $currencySymbol = Settings::first()->currency_symbol;

    return DataTables::of($query)
      ->addColumn('user', function ($record) {
        return view('_partials._profile-avatar', [
          'user' => $record->user,
        ])->render();
      })
      ->addColumn('period', fn($record) => $record->period)
      ->addColumn('status', fn($record) => ucfirst($record->status->value))
      ->editColumn('basic_salary', fn($record) => $currencySymbol . number_format($record->basic_salary, 2))
      ->editColumn('gross_salary', fn($record) => $currencySymbol . number_format($record->gross_salary, 2))
      ->editColumn('net_salary', fn($record) => $currencySymbol . number_format($record->net_salary, 2))
      ->addColumn('actions', fn($record) => view('_partials._action-icons', [
        'show' => route('payroll.show', $record->id),
      ]))
      ->editColumn('status', function ($record) {
        $html = '';
        if ($record->status == PayrollRecordStatus::PENDING) {
          $html = '<span class="badge bg-warning">Pending</span>';
        } elseif ($record->status == PayrollRecordStatus::COMPLETED || $record->status == PayrollRecordStatus::PAID) {
          $html = '<span class="badge bg-success">' . $record->status->value . '</span>';
        } else if ($record->status == PayrollRecordStatus::CANCELLED) {
          $html = '<span class="badge bg-danger">Cancelled</span>';
        } else {
          $html = '<span class="badge bg-secondary">' . $record->status->value . '</span>';
        }
        return $html;
      })
      ->rawColumns(['actions', 'status', 'user'])
      ->make(true);
  }

  /**
   * Store a manually added payroll adjustment log and recalculate totals.
   * Route: POST /payroll/records/{record}/adjustments
   * Name: payroll.adjustments.storeManual
   */
  public function storeManualAdjustment(Request $request, PayrollRecord $record): JsonResponse
  {
    if (!in_array($record->status, [PayrollRecordStatus::PENDING, PayrollRecordStatus::PROCESSED])) {
      return response()->json(['success' => false, 'message' => "Adjustments cannot be added when payroll status is '{$record->status}'."], 409);
    }

    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:191',
      'type' => ['required', Rule::in(['benefit', 'deduction'])],
      'amount' => 'required|numeric|min:0',
      'log_message' => 'nullable|string|max:500', // Use log_message for notes
    ]);

    if ($validator->fails()) {
      return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
    }
    $validated = $validator->validated();

    DB::beginTransaction();
    try {
      // Create the manual log entry
      $log = $record->payrollAdjustmentLogs()->create([
        'name' => $validated['name'],
        'type' => $validated['type'],
        'amount' => $validated['amount'],
        'log_message' => $validated['notes'] ?? null, // Map form field if needed
        'is_manual' => true, // Mark as manual
        'user_id' => $record->user_id, // Link to the employee
        'applicability' => 'employee', // Always employee for manual record adjustment
        'created_by_id' => Auth::id(), // User who added it
      ]);

      // Recalculate totals for the parent record
      $summaryData = $this->recalculatePayrollRecordTotals($record);

      DB::commit();

      // Prepare log data for JS update
      $newLogData = [
        'id' => $log->id,
        'name' => $log->name,
        'type' => $log->type,
        'amount' => $log->amount,
        'log_message' => $log->log_message,
        'is_manual' => $log->is_manual,
      ];

      return response()->json([
        'success' => true,
        'message' => 'Manual adjustment added successfully.',
        'new_log' => $newLogData, // Send new log data back
        'summary' => $summaryData // Send updated totals back
      ], 201);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error adding manual adjustment to Payroll Record {$record->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Failed to add adjustment.'], 500);
    }
  }

  /**
   * Delete a manually added payroll adjustment log and recalculate totals.
   * Route: DELETE /payroll/adjustments/log/{log} (Handles POST with _method:DELETE)
   * Name: payroll.adjustments.destroyManual
   */
  public function destroyManualAdjustment(PayrollAdjustmentLog $log): JsonResponse // Use route model binding for the log
  {
    // Authorization: Check if user can manage payroll and if log is manual
    // $record = $log->payrollRecord; // Get parent record
    // if (!$record || $record->tenant_id !== Auth::user()->tenant_id || !$log->is_manual) {
    //     abort(403);
    // }
    // if (!Auth::user()->can('manage_payroll')) { abort(403); }

    // Check if log is actually manual before deleting
    if (!$log->is_manual) {
      return response()->json(['success' => false, 'message' => 'Cannot delete system-generated adjustments.'], 403);
    }

    // Pre-condition: Check parent record status
    $record = $log->payrollRecord;
    if (!$record || !in_array($record->status, ['pending', 'processed'])) {
      return response()->json(['success' => false, 'message' => "Adjustments cannot be deleted when payroll status is '{$record->status}'."], 409);
    }


    DB::beginTransaction();
    try {
      $logId = $log->id;
      $recordId = $log->payroll_record_id;

      $log->delete(); // Delete the log entry

      // Recalculate totals for the parent record
      $summaryData = $this->recalculatePayrollRecordTotals($record); // Pass the loaded record

      DB::commit();
      Log::info("Manual Adjustment Log ID {$logId} deleted from Payroll Record ID {$recordId} by User " . Auth::id());

      return response()->json([
        'success' => true,
        'message' => 'Manual adjustment deleted successfully.',
        'summary' => $summaryData // Send updated totals back
      ]);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error deleting manual adjustment log {$log->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Failed to delete adjustment.'], 500);
    }
  }


  public function create()
  {

    $period = Carbon::now()->format('F Y');

    //Check if the payroll has already been processed
    $payroll = PayrollRecord::where('period', $period)->first();
    if ($payroll) {
      return redirect()->route('payroll.index')->with('error', 'Payroll has already been processed for this period');
    }

    //php artisan payroll:process --period="January 2025"
    Artisan::call('payroll:process', ['--period' => $period]);

    return redirect()->route('payroll.index')->with('success', 'Payroll has been processed successfully');
  }

  public function show($id)
  {
    $payroll = PayrollRecord::with(['user', 'payrollCycle', 'payrollAdjustments', 'payrollAdjustmentLogs'])->findOrFail($id);

    return view('payroll::show', [
      'payroll' => $payroll,
    ]);
  }

  public function getPayslip($id)
  {
    $payslip = Payslip::with('user')->findOrFail($id);
    return view('payroll::payslip', compact('payslip'));
  }

  public function generatePayslipPDF($payslipId)
  {
    // Fetch Payslip Details
    $payslip = Payslip::with(['user', 'payrollRecord.payrollCycle'])
      ->findOrFail($payslipId);

    $settings = Settings::first();

    $logoPath = tenant_asset('images/' . $settings->company_logo);

    $diskPath = Storage::disk('public')->get('images/' . $settings->company_logo);

    $base64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($diskPath);

    // Company Details
    $companyDetails = [
      'name' => $settings->company_name,
      'address' => $settings->company_address,
      'phone' => $settings->company_phone,
      'email' => $settings->company_email,
      'logo' => $logoPath,
      'logoBase64' => $base64
    ];

    // Prepare Data for PDF
    $data = [
      'currencySymbol' => $settings->currency_symbol,
      'payslip' => $payslip,
      'company' => $companyDetails,
    ];

    //return view('payslip.pdf', $data);

    // Load PDF View
    $pdf = Pdf::loadView('payslip.pdf', $data);

    // Optionally Save PDF on Server
    $fileName = 'payslips/' . $payslip->user->first_name . '-' . $payslip->user->last_name . '-payslip-' . now()->format('Y-m-d') . '.pdf';
    Storage::put('public/' . $fileName, $pdf->output());

    // Download PDF
    return $pdf->download('payslip-' . $payslip->user->getFullName() . '-' . $payslip->payrollRecord->period . '.pdf');
  }

  public function testGen()
  {
    $periodInput = '2025-04';
    $startOfMonth = Carbon::parse($periodInput . '-01')->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    Log::channel('payroll')->info("Manual Trigger: Starting attendance calculation for period {$periodInput}...");
    $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
    foreach ($period as $date) {
      $dateString = $date->format('Y-m-d');
      $this->info("Running attendance calculation for: {$dateString}"); // Log progress
      Log::channel('payroll')->info("Running attendance calculation for: {$dateString}");
      $exitCode = Artisan::call('payroll:attendance-calculate', ['--date' => $dateString]);
      if ($exitCode !== 0) {
        throw new Exception("Attendance calculation failed for date {$dateString}. Check logs.");
      }
    }

    Log::channel('payroll')->info("Manual Trigger: Attendance calculation finished for period {$periodInput}.");

    return redirect()->back()->with('success', 'Attendance calculation completed successfully.');
  }

  /**
   * Trigger payroll processing for a specific period via AJAX.
   * Route: POST /payroll/generate
   * Name: payroll.generate
   */
  public function generatePayroll(Request $request): JsonResponse
  {
    // Add Permission check if needed
    // abort_if_cannot('generate_payroll');

    $validator = Validator::make($request->all(), [
      'period' => 'required|date_format:Y-m', // Expecting YYYY-MM from <input type="month">
    ]);

    if ($validator->fails()) {
      return response()->json(['success' => false, 'message' => 'Invalid period selected.', 'errors' => $validator->errors()], 422);
    }

    $periodInput = $request->input('period'); // e.g., "2025-04"
    $startOfMonth = Carbon::parse($periodInput . '-01')->startOfMonth();
    $formattedPeriod = $startOfMonth->format('F Y');


    try {
      // Check if payroll for this period exists and is already processed/completed/paid
      $existingCycle = PayrollCycle::where('name', $formattedPeriod . ' Payroll')
        // Add tenant scope if needed
        ->first();

      if ($existingCycle && !in_array($existingCycle->status, ['pending', 'cancelled'])) {
        // Allow re-running if 'pending' or 'cancelled', otherwise prevent
        return response()->json(['success' => false, 'message' => "Payroll for {$formattedPeriod} has already been processed/completed with status '{$existingCycle->status}'. Cannot regenerate."], 409); // 409 Conflict
      }

      // --- Run Payroll Processing ---
      Log::channel('payroll')->info("Manual Trigger: Starting payroll processing for period: {$formattedPeriod}");
      $this->info("Running payroll process for period: {$formattedPeriod}");
      $exitCodeProcess = Artisan::call('payroll:process', ['--period' => $formattedPeriod]);

      if ($exitCodeProcess === 0) {
        Log::channel('payroll')->info("Payroll processing command completed successfully for period: {$formattedPeriod}.");
        return response()->json(['success' => true, 'message' => "Payroll generation for {$formattedPeriod} completed successfully."]); // Changed message
      } else {
        Log::channel('payroll')->error("Payroll processing command failed with exit code {$exitCodeProcess} for period: {$formattedPeriod}.");
        throw new Exception("Payroll processing command failed.");
      }
    } catch (Exception $e) {
      Log::error("Error triggering payroll generation for period {$formattedPeriod}: " . $e->getMessage());
      // Check for specific exceptions if needed
      return response()->json(['success' => false, 'message' => 'Failed to start payroll generation. Check server logs.'], 500);
    }
  }

  /**
   * Helper method to output info messages (useful inside controllers called by Artisan).
   */
  protected function info($message)
  {
    if (app()->runningInConsole()) {
      $this->command->info($message); // Use command output if available
    } else {
      Log::channel('payroll')->info($message);
    }
  }

  /**
   * Mark a Payroll Record as Completed.
   * Route: POST /payroll/records/{record}/mark-completed
   * Name: payroll.records.markCompleted
   */
  public function markAsCompleted(PayrollRecord $record): JsonResponse
  {
    // Authorization Check
    // if (!Auth::user()->can('manage_payroll') || $record->tenant_id !== Auth::user()->tenant_id) { abort(403); }

    // Check current status
    if (!in_array($record->status, [PayrollRecordStatus::PENDING, PayrollRecordStatus::PROCESSED])) {
      return response()->json(['success' => false, 'message' => 'Payroll must be in Pending or Processed state to mark as Completed.'], 409);
    }

    try {
      $record->status = PayrollRecordStatus::COMPLETED;
      $record->save();

      // Log activity?
      Log::info("Payroll Record ID {$record->id} marked as Completed by User " . Auth::id());

      return response()->json(['success' => true, 'message' => 'Payroll record marked as Completed.']);
    } catch (Exception $e) {
      Log::error("Error marking Payroll Record {$record->id} as Completed: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
    }
  }

  /**
   * Mark a Payroll Record as Paid.
   * Route: POST /payroll/records/{record}/mark-paid
   * Name: payroll.records.markPaid
   */
  public function markAsPaid(PayrollRecord $record): JsonResponse
  {
    // Authorization Check
    // if (!Auth::user()->can('mark_payroll_paid') || ...) { abort(403); }

    // Check current status - should typically be 'completed' before marking 'paid'
    if ($record->status !== PayrollRecordStatus::COMPLETED) {
      return response()->json(['success' => false, 'message' => 'Payroll must be marked as Completed before it can be marked as Paid.'], 409);
    }

    DB::beginTransaction();
    try {
      $record->status = PayrollRecordStatus::PAID;
      $record->save();

      // Update corresponding payslip status (optional)
      $payslip = $record->payslip;
      if ($payslip && $payslip->status !== 'delivered') { // Assuming 'delivered' status exists
        $payslip->status = 'delivered'; // Update based on your Payslip status enum/logic
        $payslip->save();
      }

      // Log activity? Send notification?
      Log::info("Payroll Record ID {$record->id} marked as Paid by User " . Auth::id());
      // TODO: Trigger Payslip notification/delivery?

      DB::commit();
      return response()->json(['success' => true, 'message' => 'Payroll record marked as Paid.']);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error marking Payroll Record {$record->id} as Paid: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
    }
  }

  /**
   * Cancel a Payroll Record.
   * Route: POST /payroll/records/{record}/cancel
   * Name: payroll.records.cancel
   */
  public function cancelRecord(Request $request, PayrollRecord $record): JsonResponse
  {
    // Authorization Check
    // if (!Auth::user()->can('cancel_payroll') || ...) { abort(403); }

    // Check current status - cannot cancel if already paid or cancelled
    if (in_array($record->status, [PayrollRecordStatus::PAID, PayrollRecordStatus::CANCELLED])) {
      return response()->json(['success' => false, 'message' => "Payroll record cannot be cancelled (Status: {$record->status->value})."], 409);
    }

    // Validate reason (optional but recommended)
    $validator = Validator::make($request->all(), [
      'cancel_reason' => 'required|string|max:500',
    ]);
    if ($validator->fails()) {
      return response()->json(['success' => false, 'message' => 'Cancellation reason is required.', 'errors' => $validator->errors()], 422);
    }

    DB::beginTransaction();
    try {
      $record->status = PayrollRecordStatus::CANCELLED;
      $record->cancel_remarks = $request->input('cancel_reason');
      $record->save();

      // Update corresponding payslip status (optional)
      $payslip = $record->payslip;
      if ($payslip && $payslip->status !== 'cancelled') { // Assuming 'cancelled' status exists
        $payslip->status = 'cancelled'; // Update based on your Payslip status enum/logic
        $payslip->save();
      }

      // Log activity?
      Log::info("Payroll Record ID {$record->id} cancelled by User " . Auth::id() . ". Reason: " . $request->input('cancel_reason'));

      DB::commit();
      return response()->json(['success' => true, 'message' => 'Payroll record cancelled successfully.']);
    } catch (Exception $e) {
      DB::rollBack();
      Log::error("Error cancelling Payroll Record {$record->id}: " . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'Failed to cancel payroll record.'], 500);
    }
  }


  /**
   * Recalculate and update totals on PayrollRecord and related Payslip based on adjustment logs.
   *
   * @param PayrollRecord $record
   * @return array Updated summary figures
   */
  private function recalculatePayrollRecordTotals(PayrollRecord $record): array
  {
    $logs = $record->payrollAdjustmentLogs()->get(); // Get fresh logs

    $totalBenefits = $logs->where('type', 'benefit')->sum('amount');
    $totalDeductions = $logs->where('type', 'deduction')->sum('amount');

    // Recalculate Gross and Net based on stored earned base and OT pay
    // Assuming tax amount doesn't change based on these adjustments
    $newGrossSalary = ($record->basic_salary ?? 0) + ($record->overtime_pay ?? 0) + $totalBenefits;
    $newNetSalary = $newGrossSalary - $totalDeductions - ($record->tax_amount ?? 0);

    // Update the PayrollRecord
    $record->update([
      'gross_salary' => $newGrossSalary,
      'net_salary' => $newNetSalary,
    ]);

    // Update the corresponding Payslip
    $payslip = $record->payslip; // Assumes payslip relationship exists and is singular
    if ($payslip) {
      $payslip->update([
        'total_benefits' => $totalBenefits,
        'total_deductions' => $totalDeductions,
        'net_salary' => $newNetSalary,
        'gross_salary' => $newGrossSalary, // Add if payslip has gross
      ]);
    }

    // Return data needed for JS UI update
    return [
      'basicSalary' => $record->basic_salary,
      'overtimePay' => $record->overtime_pay,
      'totalBenefits' => $totalBenefits,
      'grossSalary' => $newGrossSalary,
      'totalDeductions' => $totalDeductions,
      'taxAmount' => $record->tax_amount,
      'netSalary' => $newNetSalary,
      'currencySymbol' => Settings::first()->currency_symbol ?? '$', // Add currency symbol
    ];
  }
}
