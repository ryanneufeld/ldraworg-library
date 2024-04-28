<?php

namespace App\Livewire;

use App\Models\Part;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserDashboard extends Component
{
    #[Url]
    public string $activeTab = 'user-parts';

    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.user-dashboard');
    }
}
