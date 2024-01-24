<?php

namespace App\Livewire\Part;

use App\Models\Part;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Part $part;
    public string $image;
    public string $lib;

    public function mount(Part $part)
    {
        $this->part = $part;
        $this->part->load('events', 'history', 'subparts', 'parents');
        $this->part->events->load('part_event_type', 'user', 'part', 'vote_type');
        $this->part->votes->load('user', 'type');
        $this->lib = str_replace('/', '', $part->libFolder()); 
        $this->image = 
            $part->isTexmap() ? route("{$this->lib}.download", $part->filename) : version("images/library/{$this->lib}/" . substr($part->filename, 0, -4) . '.png');
    }

    public function toggleTracked()
    {
        if (Auth::check()) {
            Auth::user()->togglePartNotification($this->part);
        }
    }

    public function toggleDeleteFlag()
    {
        if (Auth::check() && Auth::user()->can('part.flag.delete')) {
            $this->part->delete_flag = !$this->part->delete_flag;
            $this->part->save();
        }
    }

    public function toggleManualHold()
    {
        if (Auth::check() && Auth::user()->can('part.flag.manual-hold')) {
            $this->part->manual_hold_flag = !$this->part->manual_hold_flag;
            $this->part->save();
        }
    }

    public function render()
    {
        return view('livewire.part.show')->layout('components.layout.tracker');
    }
}
