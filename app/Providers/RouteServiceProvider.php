<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * The path to the "home" route for your application.
   *
   * Typically, users are redirected here after authentication.
   *
   * @var string
   */
  public const HOME = '/';

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $this->configureRateLimiting();

    $this->routes(function () {
      Route::middleware('api')
        ->prefix('api')
        ->namespace($this->namespace)
        ->group(base_path('routes/api.php'));

      Route::middleware('web')
        ->namespace($this->namespace)
        ->group(base_path('routes/web.php'));
    });
    $this->configureRateLimiting();

    $this->mapWebRoutes();
    $this->mapApiRoutes();
  }

  protected function configureRateLimiting()
  {
    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
  }

  protected function mapWebRoutes()
  {

    foreach ($this->centralDomains() as $domain) {
      Route::middleware('web')
        ->domain($domain)
        ->namespace($this->namespace)
        ->group(base_path('routes/web.php'));
    }
  }

  protected function centralDomains(): array
  {
    if (empty(config('tenancy.central_domains')[0])) {
      return [$_SERVER['HTTP_HOST']];
    }
    return config('tenancy.central_domains');
  }

  protected function mapApiRoutes()
  {
    foreach ($this->centralDomains() as $domain) {
      Route::prefix('api')
        ->domain($domain)
        ->middleware('api')
        ->namespace($this->namespace)
        ->group(base_path('routes/api.php'));
    }
  }

}
