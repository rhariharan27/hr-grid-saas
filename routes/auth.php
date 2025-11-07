<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::middleware('web')->group(function () {

  Route::get('/activation', [BaseController::class, 'index'])->name('activation.index');
  Route::post('/activation', [BaseController::class, 'activate'])->name('activation.activate');

  Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
  Route::post('/auth/login', [AuthController::class, 'loginPost'])->name('auth.loginPost');
  Route::get('/auth/register', [AuthController::class, 'register'])->name('auth.register');
  Route::post('/auth/register', [AuthController::class, 'registerPost'])->name('auth.registerPost');

  //Non landing page route handling for the root route
  /*
  try{
      if(!Nwidart\Modules\Facades\Module::isEnabled('LandingPage')){
        Route::get('/', function () {
          return redirect()->route('login');
        });
      }
    }catch (Exception $e) {
      Route::get('/', function () {
        return redirect()->route('login');
      });
    }
  */

  Route::get('/forgot-password', function () {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.forgot-password', ['pageConfigs' => $pageConfigs]);
  })->middleware('guest')->name('password.request');

  Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
      $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
      ? back()->with('success', __($status))
      : back()->withErrors(['email' => __($status)]);
  })->middleware('guest')->name('password.email');

  Route::get('/reset-password/{token}', function (string $token) {
    $pageConfigs = ['myLayout' => 'front'];
    return view('auth.reset-password', ['token' => $token, 'pageConfigs' => $pageConfigs]);
  })->middleware('guest')->name('password.reset');

  Route::post('/reset-password', function (Request $request) {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        return redirect()->route('login')->with('success', 'Password reset successfully');
      }
    );

    return $status === Password::PASSWORD_RESET
      ? redirect()->route('login')->with('status', __($status))
      : back()->withErrors(['email' => [__($status)]]);

  })->middleware('guest')->name('password.update');

  Route::get('/email/verify', [AuthController::class, 'verifyEmail'])->name('verification.notice');

  Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', 'Verification link sent!');
  })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

  Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/')->with('success', 'Email verified, welcome to the platform!');
  })->middleware(['auth', 'signed'])->name('verification.verify');

});
