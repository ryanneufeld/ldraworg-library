<?php

namespace App\Providers;

use App\LDraw\Check\PartChecker;
use App\LDraw\LDrawModelMaker;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use App\LDraw\Parse\Parser;
use App\LDraw\PartManager;
use App\LDraw\Rebrickable;
use App\LDraw\Render\LDView;
use Illuminate\Database\Eloquent\Collection;
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
        $this->app->singleton(PartChecker::class, function (Application $app) { 
            return new PartChecker(config('ldraw.allowed_metas.body'));
        });        
        $this->app->singleton(LDrawModelMaker::class, function (Application $app) { 
            return new LDrawModelMaker();
        });
        $this->app->singleton(Parser::class, function (Application $app) { 
            return new Parser(
                config('ldraw.patterns'),
                \App\Models\PartType::pluck('type')->all(),
                \App\Models\PartTypeQualifier::pluck('type')->all(),
                config('ldraw.allowed_metas.header')
            );
        });
        $this->app->bind(LDView::class, function (Application $app) { 
            return new LDView(
                config('ldraw.render.options'),
                config('ldraw.render.alt-camera'),
                Storage::disk(config('ldraw.render.dir.ldconfig.disk'))->path(config('ldraw.render.dir.ldconfig.path')),
                config('ldraw.image.normal.height'),
                config('ldraw.image.normal.width'),
                config('ldraw.render.debug', false),
                $app->make(LDrawModelMaker::class)
            );    
        });
        $this->app->singleton(PartManager::class, function (Application $app) { 
            return new PartManager(
                $app->make(Parser::class),
                $app->make(LDView::class),
            );
        });
        $this->app->singleton(Rebrickable::class, function (Application $app) {
            return new Rebrickable(
                config('ldraw.rebrickable.api.key'),
                config('ldraw.rebrickable.api.url'),
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
        Collection::macro('unofficial', function () {
            return $this->whereNull('part_release_id');
        });
        Collection::macro('official', function () {
            return $this->whereNotNull('part_release_id');
        });    
    }
}    
