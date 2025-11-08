<?php

namespace Modules\Payroll\Http\Controllers\Api;

use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Payroll\app\Models\Payslip;

class PayrollApiController extends Controller
{
  public function generatePayslipPDF(Request $request)
  {
    $payslipId = $request->payslipId;

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


    //return file path
    return Success::response(tenant_asset($fileName));
  }

  public function getMyPayslips()
  {
    $payslips = Payslip::where('user_id', auth()->id())
      ->with(['payrollRecord.payrollCycle'])
      ->orderBy('created_at', 'desc')
      ->get();

    $payslips = $payslips->map(function ($payslip) {
      return [
        'id' => $payslip->id,
        'code' => $payslip->code,
        'basic_salary' => $payslip->basic_salary,
        'total_deductions' => $payslip->total_deductions,
        'total_benefits' => $payslip->total_benefits,
        'net_salary' => $payslip->net_salary,
        'total_worked_days' => $payslip->total_worked_days,
        'total_absent_days' => $payslip->total_absent_days,
        'total_leave_days' => $payslip->total_leave_days,
        'total_late_days' => $payslip->total_late_days,
        'total_early_checkout_days' => $payslip->total_early_checkout_days,
        'total_overtime_days' => $payslip->total_overtime_days,
        'total_holidays' => $payslip->total_holidays,
        'total_weekends' => $payslip->total_weekends,
        'total_working_days' => $payslip->total_working_days,
        'payrollAdjustments' => $payslip->payrollRecord->payrollAdjustmentLogs->map(function ($adjustment) {
          return [
            'name' => $adjustment->name,
            'code' => $adjustment->code,
            'percentage' => $adjustment->percentage,
            'amount' => $adjustment->amount,
            'type' => $adjustment->type,
          ];
        }),
        'status' => $payslip->status,
        'payroll_period' => $payslip->payrollRecord->period,
        'created_at' => $payslip->created_at->format(Constants::DateFormat),
      ];
    });

    return Success::response($payslips);
  }
}
