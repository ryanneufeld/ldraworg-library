<?php
namespace App\Tables\Filters;

use App\Models\User;
use Filament\Tables\Filters\SelectFilter;

class AuthorFilter extends SelectFilter
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->relationship('user', 'name')
            ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->realname} [{$u->name}]")
            ->native(false)
            ->searchable()
            ->preload()
            ->label('Author');
    }
}