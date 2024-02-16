<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('realname')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Select::make('part_license_id')
                    ->relationship('license', titleAttribute: 'name')
                    ->native(false)
                    ->required(),
                TextInput::make('forum_user_id')
                    ->numeric()
                    ->required(),
                Select::make('roles')
                    ->relationship('roles', titleAttribute: 'name')
                    ->mulitple()
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('realname', 'asc')
            ->columns([
                TextColumn::make('realname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('license.name')
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->wrap(),
                TextColumn::make('parts_count')
                    ->counts('parts')
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('license')
                    ->relationship('license', titleAttribute: 'name')
                    ->preload()
                    ->multiple()
                    ->native(false),
                SelectFilter::make('roles')
                    ->relationship('roles', titleAttribute: 'name')
                    ->preload()
                    ->multiple()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
