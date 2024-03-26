<?php
namespace App\Tables\Filters;

use App\Models\User;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class AuthorFilter
{
    public static function make(?string $name = null): SelectFilter
    {
        return SelectFilter::make($name)
            ->options(fn () => User::orderBy('realname', 'asc')->get()->pluck('authorString', 'id'))
            ->native(false)
            ->searchable()
            ->preload()
            ->label('Author');
    }
}