<?php

namespace App\Http\Controllers;

use App\Enums\UserAccountStatus;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Rules\ReCaptcha;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
  protected $redirectTo = RouteServiceProvider::HOME;

  public function loginPost(Request $request)
  {
    try {
      $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
        'rememberMe' => 'boolean',
        'g-recaptcha-response' => [
          'required_if:google_recaptcha_enabled,true',
          'string',
          new ReCaptcha
        ]
      ];

      $request->validate($rules);

      $user = User::where('email', $request->email)->first();

      if (!empty($user)) {

        if ($user->status != UserAccountStatus::ACTIVE) {
          return redirect()->back()->with('error', 'Your account is not active. Please contact the administrator.');
        }

        $credentials = $request->only('email', 'password');

        $role = $user->roles->first();


        if (Auth::attempt($credentials)) {
          if (tenancy()->initialized) {
            if ($role->name == 'admin') {
              return redirect()->route('tenant.dashboard')->with('success', 'Welcome back!');
            } else {
              return redirect()->route('user.dashboard.index')->with('success', 'Welcome back!');
            }
          }

          if ($role->name == 'super_admin' && empty($user->tenant_id)) {
            $request->session()->regenerate();
            return redirect()->route('superAdmin.dashboard')->with('success', 'Welcome back!');
          } else {
            return redirect()->back()->with('error', __('You are not authorized to access this resource.'));
          }
        } else {
          return redirect()->back()->with('error', __('Invalid username or password.'));
        }
      } else {
        return redirect()->back()->with('error', __('User not found.'));
      }
    } catch (Exception $e) {
      Log::info($e->getMessage());
      return redirect()->back()->with('error', 'Oops! You have entered invalid credentials');
    }
  }

  public function register()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.register', ['pageConfigs' => $pageConfigs]);
  }

  public function registerPost(Request $request)
  {
    $rules = [
      'firstName' => 'required|string',
      'lastName' => 'required|string',
      'gender' => 'required|string',
      'phone' => 'required|string|unique:users,phone',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6',
      'g-recaptcha-response' => [
        'required_if:google_recaptcha_enabled,true',
        'string',
        new ReCaptcha
      ]
    ];

    $request->validate($rules);

    $user = new User();
    $user->first_name = $request->firstName;
    $user->last_name = $request->lastName;
    $user->gender = $request->gender;
    $user->phone = $request->phone;
    $user->email = $request->email;
    $user->password = bcrypt($request->password);
    $user->email_verified_at = env('APP_DEMO') ? now() : null;
    $user->save();

    $user->assignRole('customer');

    auth()->login($user);

    if (!env('APP_DEMO')) {
      $user->sendEmailVerificationNotification();
    }

    return redirect()->route('verification.notice')->with('success', 'Account created successfully, please verify your email address.');
  }

  public function login()
  {

    if (auth()->user()) {
      return redirect('/');
    }

    /*   if (auth()->user()) {

         if (auth()->user()->hasRole('super_admin')) {
           return redirect()->route('superAdmin.dashboard')->with('success', 'Welcome back!');
         } else {

           if(tenancy()->initialized)
           {
             return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
           }

           if (auth()->user()->email_verified_at == null) {
             return redirect()->route('verification.notice')->with('error', 'Please verify your email address');
           }
           if(auth()->user()->hasRole('user')) {
             return redirect()->route('customer.dashboard')->with('success', 'Welcome back!');
           }else{
             return redirect()->route('tenant.dashboard')->with('success', 'Welcome back!');
           }
         }
       }*/

    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.login', ['pageConfigs' => $pageConfigs]);
  }

  public function logout()
  {
    if (Cache::has('accessible_module_routes')) {
      Cache::forget('accessible_module_routes');
    }
    auth()->logout();
    return redirect('auth/login')->with('success', 'Successfully logged out');
  }

  public function verifyEmail()
  {
    if (auth()->user()->hasVerifiedEmail()) {
      return redirect('/')->with('success', 'Email already verified');
    }
    $pageConfigs = ['myLayout' => 'blank'];
    return view('auth.verify-email', ['pageConfigs' => $pageConfigs]);
  }

}
