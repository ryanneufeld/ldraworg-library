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
use Filament\Tables\Table as Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

class PartList extends Component implements HasForms, HasTable
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
            ->query($this->unofficial ? Part::unofficial() : Part::official())
            ->defaultSort('vote_sort', 'asc')
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns([
                ImageColumn::make('image')
                    ->state( 
                        fn (Part $p): string => asset("images/library/{$p->libFolder()}" . substr($p->filename, 0, -4) . '_thumb.png')
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('filename')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('description')
                    ->wrap()
                    ->sortable(),
                 TextColumn::make('download')
                    ->state( 
                        fn (Part $p): string => 
                            "<a href=\"" .
                            route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename) . 
                            "\">[DAT]</a>"
                    )
                    ->html(),
                ViewColumn::make('vote_sort')
                    ->view('tables.columns.part-status')
                    ->sortable()
                    ->label('Status'),
                
            ])
            ->filters([
                SelectFilter::make('vote_sort')
                    ->options([
                        '1' => 'Certified',
                        '2' => 'Needs Admin Review',
                        '3' => 'Needs More Votes',
                        '5' => 'Errors Found'
                    ])
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->label('Status')
                    ->visible($this->unofficial),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->label('Author'),
                SelectFilter::make('part_type_id')
                    ->relationship('type', 'name')
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->label('Part Type'),
            ], layout: FiltersLayout::AboveContent)
                ->persistFiltersInSession()
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
        return view('livewire.part.part-list')->layout('components.layout.tracker');
    }
}
