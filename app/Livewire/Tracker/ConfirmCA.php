<?php

namespace App\Livewire\Tracker;

use App\Models\PartLicense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ConfirmCA extends Component
{
    public function updateLicense()
    {
        $user = Auth::user();
        $user->license()->associate(PartLicense::default());
        $user->save();
        return $this->redirectRoute(session('ca_route_redirect'));   
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.tracker.confirm-c-a');
    }
}
