<?php

namespace App\Http\Livewire\Part;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Part;

class Show extends Component
{
    public Part $part;

    protected $listeners = [
        'updateSubpart' => 'updateSubpart',
        'updateImage' => 'updateImage',
    ];

    public function render()
    {
        return view('livewire.part.show', ['lib' => str_replace('/', '', $this->part->libFolder())])
            ->layout('components.layout.tracker');
    }

    public function toggleTracking()
    {
        if (Auth::check()) {
            Auth::user()->togglePartNotification($this->part);
        }
    }

    public function toggleDelete()
    {
        $this->part->delete_flag = !$this->part->delete_flag;
        $this->part->save();
    }

    public function updateSubpart()
    {
        $this->part->updateSubparts();
        session()->flash('status', 'Part dependencies updated');
    }

    public function updateImage()
    {
        $this->part->updateImage();
//        session()->flash('status', 'Image updated');
    }

}
