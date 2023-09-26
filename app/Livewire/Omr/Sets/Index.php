<?php

namespace App\Livewire\Omr\Sets;

use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    
    public function render()
    {
        $this->dispatch('jquery');
        return view('livewire.omr.sets.index', [
            'sets' => \App\Models\Omr\Set::has('models')->orderBy('number')->paginate(100),
        ]);
        return view('livewire.omr.sets.index');
    }
}
