<?php

namespace App\Livewire\Tracker;

use App\Jobs\UserChangePartUpdate;
use App\Models\PartLicense;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConfirmCA extends Component
{
    public function updateLicense()
    {
        $user = Auth::user();
        $olddata['part_license_id'] = $user->part_license_id;
        $user->license()->associate(PartLicense::default());
        $user->save();
        UserChangePartUpdate::dispatch($user, $olddata);
        return $this->redirectRoute(session('ca_route_redirect'));   
    }

    public function render()
    {
        return view('livewire.tracker.confirm-c-a')->layout('components.layout.tracker');
    }
}
