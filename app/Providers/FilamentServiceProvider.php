<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'red' => Color::Red,
            'yellow' => Color::Yellow,
            'blue' => Color::Blue,
        ]);
        
        Select::configureUsing(function (Select $select): void {
            $select
                ->optionsLimit(-1)
                ->native(false);
        });

        SelectFilter::configureUsing(function (SelectFilter $selectfilter): void {
            $selectfilter
                ->optionsLimit(-1)
                ->native(false);
        });
    }
}
