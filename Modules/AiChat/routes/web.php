<?php

use App\Http\Middleware\AddonCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\AiChat\Http\Controllers\AiChatController;
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
  $request->headers->set('addon', ModuleConstants::AI_CHATBOT);
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
      Route::get('/aiChat', function () {
        return view('aichat::index');
      })->name('aiChat.index');
      Route::post('/aiChat/query', [AiChatController::class, 'handleQuery']);
      Route::get('/test', [AiChatController::class, 'test']);
      Route::get('/getSchema', [AiChatController::class, 'getSchema']);
    });
  });
});
