<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use App\LDraw\Check\PartChecker;
use App\LDraw\Parse\Parser;
use App\LDraw\PartManager;
use App\LDraw\Render\LDrawPng;
use App\LDraw\Render\LDView;
use Illuminate\Support\Facades\Storage;

class LDrawServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Parser::class, function (Application $app) { 
            return new Parser(
            config('ldraw.patterns'), 
            \App\Models\PartType::pluck('type')->all(), 
            \App\Models\PartTypeQualifier::pluck('type')->all(),
            config('ldraw.allowed_metas.header'));
        });
        $this->app->singleton(LDView::class, function (Application $app) { 
            return new LDView(
                config('ldraw.render.options'),
                config('ldraw.render.alt-camera'),
                config('ldraw.staging_dir.disk'),
                config('ldraw.staging_dir.path'),
                Storage::disk(config('ldraw.render.dir.ldconfig.disk'))->path(config('ldraw.render.dir.ldconfig.path')),
                config('ldraw.image.normal.height'),
                config('ldraw.image.normal.width'),
                $app->make(PartManager::class)
            );    
        });
        $this->app->singleton(LDrawPNG::class, function (Application $app) { 
            return new LDrawPng(
                config('ldraw.staging_dir.disk'),
                config('ldraw.staging_dir.path'),
            );
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
