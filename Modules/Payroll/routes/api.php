<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\Api\PayrollApiController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware([
  'api',
  InitializeTenancyByDomain::class,
  PreventAccessFromCentralDomains::class
])->group(function () {
  Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'V1'], function () {
      Route::group([
        'middleware' => 'api',
        'as' => 'api.',
      ], function ($router) {
        Route::get('payroll/downloadPdf', [PayrollApiController::class, 'generatePayslipPDF'])->name('payroll.payslipPdf');
        Route::get('payroll/getMyPayslips', [PayrollApiController::class, 'getMyPayslips'])->name('payroll.getMyPayslips');
      });
    });
  });
});
