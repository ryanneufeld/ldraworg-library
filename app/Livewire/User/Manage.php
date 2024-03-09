<?php

namespace App\Livewire\User;

use App\Jobs\UpdateMybbUser;
use App\Jobs\UserChangePartUpdate;
use App\Models\MybbUser;
use App\Models\PartLicense;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table as Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Manage extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->defaultSort('realname', 'asc')
            ->columns([
                TextColumn::make('realname')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->visible(Auth::user()?->can('user.view.email')),
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
            ->headerActions([
                CreateAction::make()
                ->form($this->formSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['password'] = bcrypt(Str::random(40));
                    return $data;            
                })
                ->after(function (User $user) {
                    if (app()->environment() == 'production') {
                        UpdateMybbUser::dispatch($user);
                    } else {
                        Log::debug("User update job run for {$user->name}");
                    }
                })
                ->visible(Auth::user()?->can('user.add'))
            ])
            ->actions([
                EditAction::make()
                    ->form($this->formSchema())
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
                    ->visible(Auth::user()?->can('user.modify'))
                    ->after(function (User $user) {
                        if (app()->environment() == 'production') {
                            UpdateMybbUser::dispatch($user);
                        } else {
                            Log::debug("User update job run for {$user->name}");
                        }
                    }),
            ]);
    }

    protected function formSchema(): array
    {
        return [
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
            ];
    }

    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.user.manage');
    }
}