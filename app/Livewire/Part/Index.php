<?php

namespace App\Livewire\Part;

use App\Models\Part;
use App\Models\User;
use App\Tables\Filters\AuthorFilter;
use App\Tables\Part\PartTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table as Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Url;

class Index extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public bool $unofficial = false;

    #[Url()]
    public ?array $tableFilters = null;
    #[Url]
    public ?string $tableSortColumn = null;
    #[Url]
    public ?string $tableSortDirection = null;
    #[Url]
    public $tableRecordsPerPage = null;

    public function mount()
    {
        $this->unofficial = Route::currentRouteName() == 'official.index' ? false : true;
    }

    public function table(Table $table): Table
    {
        return PartTable::table($table, !$this->unofficial);
    }

    public function render()
    {
        return view('livewire.part.part-list')->layout('components.layout.tracker');
    }
}
