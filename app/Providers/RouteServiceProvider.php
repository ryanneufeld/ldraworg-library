<?php

namespace App\Providers;

use App\Models\Omr\Set;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Models\Part;
use Illuminate\Database\Eloquent\Builder;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
        Route::pattern('officialpart', '[a-z0-9_/-]+\.(dat|png)');
        Route::pattern('unofficialpart', '[a-z0-9_/-]+\.(dat|png)');
        Route::pattern('setnumber', '[a-z0-9]+(-\d+)?');
        Route::bind('officialpart', fn (string $value): Part =>
            Part::official()->where('filename', $value)->firstOrFail()
        );
        Route::bind('unofficialpart', fn (string $value): Part =>
            Part::unofficial()->where('filename', $value)->firstOrFail()
        );
        Route::bind('setnumber', fn (string $value): Set =>
            Set::where(fn (Builder $q) =>
                $q->orWhere('number', $value)->orWhere('number', "{$value}-1")
            )
            ->firstOrFail()
        );
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('file', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
