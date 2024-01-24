<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
        Model::preventSilentlyDiscardingAttributes(!$this->app->isProduction());
        //Model::preventLazyLoading(! $this->app->isProduction());
    }
}
