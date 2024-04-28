<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Admin extends Component
{
    #[Url]
    public string $activeTab = 'admin-ready';

    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.dashboard.admin');
    }
}
