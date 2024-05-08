<?php

namespace App\Livewire\Dashboard\Admin\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Livewire\Attributes\Layout;
use Livewire\Component;

abstract class BasicResourceManagePage extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $title = '';

    abstract public function table(Table $table): Table;

    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.dashboard.admin.pages.basic-resource-manage-page');
    }
}
