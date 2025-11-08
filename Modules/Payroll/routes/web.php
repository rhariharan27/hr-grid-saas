<?php

use App\Http\Middleware\AddonCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\Payroll\app\Http\Controllers\PayrollController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => function ($request, $next) {
  $request->headers->set('addon', ModuleConstants::PAYROLL);
  return $next($request);
}], function () {
  Route::middleware([
    'web',
    'auth',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    AddonCheckMiddleware::class,
  ])->group(function () {
    Route::group([], function () {
      Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
      Route::get('payroll/getListAjax', [PayrollController::class, 'getListAjax'])->name('payroll.getListAjax');
      Route::get('payroll/payslip/{id}', [PayrollController::class, 'getPayslip'])->name('payroll.payslip');
      Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
      Route::get('payroll/show/{id}', [PayrollController::class, 'show'])->name('payroll.show');
      Route::get('payroll/{id}/pdf', [PayrollController::class, 'generatePayslipPDF'])
        ->name('payslip.pdf');

      Route::post('/generate', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
      Route::get('testGen', [PayrollController::class, 'testGen'])->name('payroll.testGen');

      Route::prefix('payroll')->name('payroll.')->group(function () {
        // ... existing payroll routes (index, getListAjax, show, generate, payslip.pdf) ...

        // Route for deleting a specific Adjustment Log (use log ID)
        Route::delete('/adjustments/log/{log}', [PayrollController::class, 'destroyManualAdjustment'])->name('adjustments.destroyManual'); // Use DELETE verb


        Route::prefix('records/{record}')->name('records.')->group(function () { // Group by record
          Route::put('/mark-completed', [PayrollController::class, 'markAsCompleted'])->name('markCompleted');
          Route::put('/mark-paid', [PayrollController::class, 'markAsPaid'])->name('markPaid');
          Route::post('/cancel', [PayrollController::class, 'cancelRecord'])->name('cancel');

          // Route for manual adjustments (ensure {record} matches parameter name)
          Route::post('/adjustments', [PayrollController::class, 'storeManualAdjustment'])->name('adjustments.storeManual');
        });

      });
    });
  });
});
