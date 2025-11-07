<?php

namespace App\Services\AddonService;

use App\Models\Settings;
use App\Models\SuperAdmin\SaSettings;
use App\Services\PlanService\ISubscriptionService;
use ModuleConstants;
use Nwidart\Modules\Facades\Module;

class AddonService implements IAddonService
{

  private ISubscriptionService $subscriptionService;

  function __construct(ISubscriptionService $subscriptionService)
  {
    $this->subscriptionService = $subscriptionService;
  }

  public function getAvailableAddons()
  {
    $settings = Settings::first();

    if (!$settings->accessible_module_routes) {
      $this->subscriptionService->processTenantSettingsForAccessRoutesByPlan();
    }

    return $settings->available_modules;
  }

  /*
   * Check if the addon is enabled
   * @param string $name
   * @param bool $isStandard
   * @return bool
   */

  public function isAddonEnabled(string $name, bool $isStandard = false): bool
  {
    if (tenancy()->initialized) {
      $addons = Settings::first()->available_modules;

      if ($isStandard) {
        return in_array($name, $addons);
      }

      $module = Module::find($name);

      $result = ($module != null && $module->isEnabled() && in_array($name, $addons));

      //Log::info('Is Addon Enabled: ' . $result);

      return $result;
    } else {
      $module = Module::find($name);

      return ($module != null && $module->isEnabled());
    }
  }

  public function isSAAddonEnabled(string $name): bool
  {
    $module = Module::find($name);

    return ($module != null && $module->isEnabled());
  }

  public function isStripeEnabled(): bool
  {
    return $this->isSAAddonEnabled(ModuleConstants::STRIPE_GATEWAY) && SaSettings::first()->stripe_enabled;
  }
}
