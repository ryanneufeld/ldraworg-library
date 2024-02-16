<?php

namespace App\Livewire\Part;

use App\Models\Part;
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
            ->columns([
                ImageColumn::make('image')
                    ->state( 
                        fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('filename')
                    ->description(fn (Part $p): string => $p->description)
                    ->wrap(),
                TextColumn::make('download')
                    ->state('[DAT]')
                    ->url(fn (Part $p) => route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename)),
                ViewColumn::make('vote_sort')
                    ->view('tables.columns.part-status')
                    ->label('Status'),
                
            ])
            ->groups([
                Group::make('week')
                    ->date(),
            ]) 
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
