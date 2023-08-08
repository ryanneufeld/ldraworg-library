<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    } 

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('local')) {
            Mail::alwaysTo('orion@ldraw.org');
        }
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());
        //Model::preventLazyLoading(! $this->app->isProduction());
    }
}
