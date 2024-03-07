<?php

namespace App\Livewire\Part;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table as Table;
use Livewire\Component;
use Illuminate\Support\Facades\Route;

class Weekly extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;


    public function table(Table $table): Table
    {

        return $table
            ->query(Part::unofficial()->whereNull('official_part_id'))
            ->defaultSort('created_at', 'asc')
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns(PartTable::columns())
            ->groups([
                Group::make('week')
                    ->date(),
            ])
            ->actions(PartTable::actions()) 
            ->defaultGroup('week')   
            ->recordUrl(
                fn (Part $p): string => 
                    route($p->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $p])
            )
            ->striped()
            ->paginated([50, 100, 250, 500])
            ->defaultPaginationPageOption(50);
    }

    public function render()
    {
        return view('livewire.part.weekly')->layout('components.layout.tracker');
    }
}
