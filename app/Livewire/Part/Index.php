<?php

namespace App\Livewire\Part;

use App\Models\Part;
use App\Models\User;
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
            ->query(
                Part::when(
                    $this->unofficial, 
                    fn (Builder $q) => $q->unofficial(),
                    fn (Builder $q) => $q->official()
                )
            )
            ->defaultSort(fn (Builder $q) => $q->orderBy('vote_sort')->orderBy('part_type_id')->orderBy('description', 'asc'))
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->state( 
                            fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                        )
                        ->grow(false)
                        ->extraImgAttributes(['class' => 'object-scale-down w-[35px] max-h-[75px]']),
                    Stack::make([
                        TextColumn::make('filename')
                        ->weight(FontWeight::Bold)
                        ->sortable(),
                    TextColumn::make('description')
                        ->sortable(),
                    ])->alignment(Alignment::Start),
                    ViewColumn::make('vote_sort')
                        ->view('tables.columns.part-status')
                        ->sortable()
                        ->grow(false)
                        ->label('Status')
                ])->from('md')
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
                    ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->realname} [{$u->name}]")
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
                SelectFilter::make('part_license_id')
                    ->relationship('license', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->label('Part License'),
                    
            ], layout: FiltersLayout::AboveContent)
            ->persistFiltersInSession()
            ->actions([
                Action::make('download')
                    ->url(fn(Part $part) => route($part->isUnofficial() ? 'unofficial.download' : 'official.download', $part->filename))
                    ->button()
                    ->color('info'),
                Action::make('updated')
                    ->url(fn(Part $part) => route('tracker.show', $part->unofficial_part_id))
                    ->label(fn(Part $part) => ' Tracker Update: ' . $part->unofficial_part->statusCode())
                    ->button()
                    ->outlined()
                    ->visible(fn(Part $part) => !is_null($part->unofficial_part_id)),
            ])
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
