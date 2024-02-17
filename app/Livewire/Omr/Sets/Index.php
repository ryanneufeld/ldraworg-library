<?php

namespace App\Livewire\Omr\Sets;

use App\Models\Omr\OmrModel;
use App\Models\Omr\Set;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table as Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

class Index extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public bool $unofficial = false;

    public function mount()
    {
        $this->unofficial = Route::currentRouteName() == 'official.index' ? false : true;
    }

    public function table(Table $table): Table
    {

        return $table
            ->query(OmrModel::query())
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns([
                ImageColumn::make('set.rb_url')
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('set.number')
                    ->label('Set Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('set.name')
                    ->label('Set Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->state(fn(OmrModel $m) => $m->alt_model_name ?? 'Main Model'),
                TextColumn::make('user')
                    ->state(fn(OmrModel $m) => $m->user->authorString())
                    ->sortable(),
            ])
            ->filters([
            ], layout: FiltersLayout::AboveContent)
                ->persistFiltersInSession()
            ->recordUrl(
                fn (OmrModel $m): string => 
                    route('omr.sets.show', $m->set)
            )
            ->striped()
            ->paginated([50, 100, 250, 500])
            ->defaultPaginationPageOption(50);
    }

    public function render()
    {
        return view('livewire.tables.basic-table')->layout('components.layout.omr');
    }
}
