<?php

namespace App\Livewire\Dashboard\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $activeTab = 'admin-ready';

    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.dashboard.admin.index');
    }
}
