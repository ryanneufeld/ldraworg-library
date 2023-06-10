<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use App\LDraw\PartChecker;

class LDrawServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->singleton(PartChecker::class, function (Application $app) { 
        return new PartChecker;
      });
    } 

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}    
