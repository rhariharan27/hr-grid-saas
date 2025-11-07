<?php

namespace App\Services\AddonService;

interface IAddonService
{
  public function getAvailableAddons();

  public function isAddonEnabled(string $name, bool $isStandard = false): bool;



  //SA Addon

  public function isSAAddonEnabled(string $name) : bool;

  public function isStripeEnabled() : bool;
}
