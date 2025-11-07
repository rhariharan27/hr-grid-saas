<?php

namespace App\Rules;

use App\Models\SuperAdmin\SaSettings;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class ReCaptcha implements ValidationRule
{
  /**
   * Run the validation rule.
   *
   * @param Closure(string): PotentiallyTranslatedString $fail
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $response = Http::get("https://www.google.com/recaptcha/api/siteverify", [
      'secret' => SaSettings::first()->google_recaptcha_secret_key,
      'response' => $value
    ]);

    if (!($response->json()["success"] ?? false)) {
      $fail('The google recaptcha is required.');
    }
  }
}
