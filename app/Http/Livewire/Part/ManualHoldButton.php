<?php

namespace App\Http\Livewire\Part;

use Livewire\Component;
use App\Models\Part;

class ManualHoldButton extends Component
{
    public Part $part;

    protected $rules = [
        'part.manual_hold_flag' => 'required|boolean',
    ];

    public function render()
    {
        return view('livewire.part.manual-hold-button');
    }

    public function toggleFlag()
    {
        $this->part->manual_hold_flag = !$this->part->manual_hold_flag;
        $this->part->save();
    }
}
