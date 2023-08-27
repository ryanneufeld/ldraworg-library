<?php

namespace App\Livewire\Part;

use Livewire\Component;
use App\Models\Part;

class DeleteFlagButton extends Component
{
    public Part $part;

    protected $rules = [
        'part.delete_flag' => 'required|boolean',
    ];

    public function render()
    {
        return view('livewire.part.delete-flag-button');
    }

    public function toggleFlag()
    {
        $this->part->delete_flag = !$this->part->delete_flag;
        $this->part->save();
    }
}
