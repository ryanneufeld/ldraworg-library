<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Jobs\UpdateMybbUser;
use App\Jobs\UserChangePartUpdate;
use App\Models\MybbUser;
use App\Models\PartLicense;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                    ->default(PartLicense::default()->id)
                    ->native(false)
                    ->required(),
                Select::make('forum_user_id')
                    ->label('Forum User Name')
                    ->options(
                        MybbUser::whereNotIn('usergroup', [5,7])
                            ->whereNotIn('uid', User::whereNotNull('forum_user_id')->pluck('forum_user_id')->all())
                            ->orderBy('loginname')
                            ->pluck('loginname', 'uid')
                    )
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, int $state) {
                            $user = MybbUser::find($state);
                            if (!is_null($user)) {
                                $set('realname', $user->username);
                                $set('name', $user->loginname);
                                $set('email', $user->email);
                            }
                    })
                    ->hiddenOn('edit'),
                Select::make('roles')
                    ->relationship('roles', titleAttribute: 'name')
                    ->multiple()
                    ->native(false)
                    ->preload()
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
                Tables\Actions\EditAction::make()
                    ->afterFormValidated(function (array $data, User $user) {
                        $olddata = [];
                        if ($data['name'] != $user->name) {
                            $olddata['name'] = $user->name;
                        }
                        if ($data['realname'] != $user->realname) {
                            $olddata['realname'] = $user->realname;
                        }
                        if ($data['part_license_id'] != $user->part_license_id) {
                            $olddata['part_license_id'] = $user->part_license_id;
                        }
                        if (app()->environment() == 'production') {
                           if (!empty($olddata)) {
                                UserChangePartUpdate::dispatch($user, $olddata);
                            }
                        } else {
                            Log::debug('User data update', ['newdata' => $data, 'olddata' => $olddata]);
                        }             
                    })
                    ->after(function (User $user) {
                        if (app()->environment() == 'production') {
                            UpdateMybbUser::dispatch($user);
                        } else {
                            Log::debug("User update job run for {$user->name}");
                        }
                    }),
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
