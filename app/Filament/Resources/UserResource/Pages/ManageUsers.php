<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\UpdateMybbUser;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
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
                }),
        ];
    }
}
