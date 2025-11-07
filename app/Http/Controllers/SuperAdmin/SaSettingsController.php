<?php

namespace App\Http\Controllers\SuperAdmin;

use App\ApiClasses\Error;
use App\ApiClasses\Success;
use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\SaSettings;
use Illuminate\Http\Request;

class SaSettingsController extends Controller
{
  public function index()
  {
    $saSettings = SaSettings::first();
    return view('superAdmin.settings.index', compact('saSettings'));
  }

  public function paymentGatewayUpdate(Request $request)
  {
    if (env('APP_DEMO')) {
      return redirect()->back()->with('error', 'This feature is disabled in the demo.');
    }

    $request->validate([
      'paypal' => 'nullable',
      'razorpay' => 'nullable',
      'offline' => 'nullable',
      'stripe' => 'nullable',
    ]);

    $saSettings = SaSettings::first();

    if ($request->has('paypal')) {
      $validated = $request->validate([
        'paypalClientId' => 'required',
        'paypalSecret' => 'required',
        'paypalMode' => 'required',
      ]);

      $saSettings->paypal_client_id = $validated['paypalClientId'];
      $saSettings->paypal_secret = $validated['paypalSecret'];
      $saSettings->paypal_mode = $validated['paypalMode'];
      $saSettings->paypal_enabled = true;
    }

    if ($request->has('razorpay')) {
      $validated = $request->validate([
        'razorpayKey' => 'required',
        'razorpaySecret' => 'required',
      ]);

      $saSettings->razorpay_key = $validated['razorpayKey'];
      $saSettings->razorpay_secret = $validated['razorpaySecret'];
      $saSettings->razorpay_enabled = true;
    }

    if ($request->has('offline')) {
      $validated = $request->validate([
        'offlineInstructions' => 'required',
      ]);

      $saSettings->offline_payment_enabled = true;
      $saSettings->offline_payment_instructions = $validated['offlineInstructions'];
    }

    if ($request->has('stripe')) {
      $validated = $request->validate([
        'stripePublishableKey' => 'required',
        'stripeSecretKey' => 'required',
      ]);

      $saSettings->stripe_publishable_key = $validated['stripePublishableKey'];
      $saSettings->stripe_secret_key = $validated['stripeSecretKey'];
      $saSettings->stripe_enabled = true;
    }

    $saSettings->save();

    return redirect()->route('saSettings.index')->with('success', 'Payment gateway settings updated successfully');
  }

  public function changePaymentGatewayStatusAjax($gateway)
  {
    if (env('APP_DEMO')) {
      return Error::response('This feature is disabled in the demo.');
    }

    $saSettings = SaSettings::first();
    if ($gateway == 'paypalSettings') {
      if (!$saSettings->paypal_enabled && !empty($saSettings->paypal_client_id) && !empty($saSettings->paypal_secret)) {
        $saSettings->paypal_enabled = true;
      }
    } else if ($gateway == 'razorpaySettings') {
      if (!$saSettings->razorpay_enabled && !empty($saSettings->razorpay_key) && !empty($saSettings->razorpay_secret)) {
        $saSettings->razorpay_enabled = true;
      }
    } else if ($gateway == 'offlineSettings') {
      if (!$saSettings->offline_payment_enabled && !empty($saSettings->offline_payment_instructions)) {
        $saSettings->offline_payment_enabled = true;
      }
    } else if ($gateway == 'stripeSettings') {
      if (!$saSettings->stripe_enabled && !empty($saSettings->stripe_publishable_key) && !empty($saSettings->stripe_secret_key)) {
        $saSettings->stripe_enabled = true;
      }
    }

    $saSettings->save();

    return Success::response('Payment gateway disabled successfully');
  }

  public function update(Request $request)
  {
    if (env('APP_DEMO')) {
      return redirect()->back()->with('error', 'This feature is disabled in the demo.');
    }

    $validated = $request->validate([
      'appVersion' => 'required',
      'currency' => 'required',
      'currencySymbol' => 'required',
      'currencyPosition' => 'required',
      'privacyPolicyUrl' => 'required',
      'isPerTenantMapKeyEnabled' => 'nullable'
    ]);

    $saSettings = SaSettings::first();
    $saSettings->app_version = $validated['appVersion'];

    if ($request->has('appForceUpdate')) {
      $saSettings->app_force_update = true;
    }

    $saSettings->currency = $validated['currency'];
    $saSettings->currency_symbol = $validated['currencySymbol'];
    $saSettings->currency_position = $validated['currencyPosition'];
    $saSettings->privacy_policy_url = $validated['privacyPolicyUrl'];

    if ($request->isPerTenantMapKeyEnabled == 'on') {
      $saSettings->use_per_tenant_map_key = true;
    } else {
      $saSettings->use_per_tenant_map_key = false;
    }

    $saSettings->save();

    return redirect()->route('saSettings.index')->with('success', 'Settings updated successfully');
  }
}
