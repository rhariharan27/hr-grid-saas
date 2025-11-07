<?php

use Illuminate\Support\Facades\Route;
use Modules\DataImportExport\App\Http\Controllers\DataImportExportController;
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
  $request->headers->set('addon', ModuleConstants::DATA_IMPORT_EXPORT);
  return $next($request);
}], function () {
  Route::middleware([
    'api',
    'auth',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
  ])->group(function () {
    Route::prefix('dataImportExport')->group(function () {
      Route::get('/', [DataImportExportController::class, 'index'])->name('dataImportExport.index');
      Route::post('import', [DataImportExportController::class, 'import'])->name('dataImportExport.import');
      Route::get('export/{type}', [DataImportExportController::class, 'export'])->name('dataImportExport.export');
    });
  });
});
