<?php

namespace App\Livewire\Part;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Part;

class TrackButton extends Component
{
    public Part $part;

    public function render()
    {
        return view('livewire.part.track-button');
    }

    public function toggleFlag()
    {
        if (Auth::check()) {
            Auth::user()->togglePartNotification($this->part);
        }
    }
}
