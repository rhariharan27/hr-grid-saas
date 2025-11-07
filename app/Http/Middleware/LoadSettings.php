<?php

namespace App\Http\Middleware;

use App\Models\Settings;
use App\Models\SuperAdmin\SaSettings;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ModuleConstants;
use Modules\LandingPage\app\Models\LandingPageSetting;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\Response;

class LoadSettings
{
  /**
   * Handle an incoming request.
   *
   * @param Closure(Request): (Response) $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (tenancy()->initialized) {
      $settings = Cache::remember('app_settings', 60 * 60 * 24, function () {
        return Settings::first();
      });

      view()->share('settings', $settings);

    } else {
      $settings = Cache::remember('sa_app_settings', 60 * 60 * 24, function () {
        return SaSettings::first();
      });

      view()->share('settings', $settings);

      //TODO: Need to check if the module is enabled Landing Page
      try {
        if (Module::isEnabled(ModuleConstants::LANDING_PAGE)) {
          view()->share('landingSettings', LandingPageSetting::current());
        }
      } catch (Exception $exception) {

      }
    }

    return $next($request);
  }
}
