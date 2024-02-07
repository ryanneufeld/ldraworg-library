<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;

class SearchParts extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    #[Modelable]
    public array $data;
    public $unofficial;

    #[On ('search-updated')]
    public function searchUpdated() {
        $this->resetTable();
        $this->render();
    }

    public function table(Table $table): Table
    {
        return $table
        ->query(
            Part::searchPart($this->data['search'] ?? '', in_array($this->data['scope'] ?? '', ['filename', 'description', 'header', 'file']) ? $this->data['scope'] : 'header')
                ->when(
                    $this->unofficial === true,
                    fn ($q) => $q->unofficial(),
                    fn ($q) => $q->official()
                )
                ->when(
                    is_numeric($this->data['user_id'] ?? ''),
                    function ($q) {
                        $opr = ($this->data['exclude_user'] ?? false) ? '!=' : '=';
                        if ($this->data['include_history'] ?? false) {
                            $q->where(function ($q) use ($opr) {
                                $q->orWhere('user_id', $opr, $this->data['user_id'])->orWhereHas('history', function($qu) use ($opr) {
                                    $qu->where('user_id', $opr, $this->data['user_id']);
                                });
                            });
                        } else {
                            $q->where('user_id', $opr, $this->data['user_id']);
                        }        
                    })
                ->when(
                    $this->unofficial && ($this->data['status'] ?? '') != '',
                    fn ($q) => $q->partStatus($this->data['status'])
                )
                ->when(
                    count($this->data['part_type_id'] ?? []) > 0,
                    fn ($q) => $q->whereIn('part_type_id', $this->data['part_type_id'])
                )
        )
        ->heading($this->unofficial ? 'Unofficial Part Results' : 'Official Part Results')
        ->defaultSort('filename', 'asc')
        ->emptyState(view('tables.empty', ['none' => 'None']))
        ->columns([
            ImageColumn::make('image')
                ->state( 
                    fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                )
                ->extraImgAttributes(['class' => 'object-scale-down']),
            TextColumn::make('filename')
                ->wrap()
                ->sortable(),
            TextColumn::make('description')
                ->wrap()
                ->sortable(),
            TextColumn::make('download')
                ->state('[DAT]')
                ->url(fn (Part $p) => route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename)),
            ViewColumn::make('vote_sort')
                ->view('tables.columns.part-status')
                ->sortable()
                ->label('Status')
                ->visible($this->unofficial === true),
            
        ])
        ->recordUrl(
            fn (Part $p): string => 
                route($p->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $p])
        )
        ->striped();
    }

    public function render(): View
    {
        return view('livewire.tables.basic-table');
    }
}
