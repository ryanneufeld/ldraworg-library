<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

class RelatedParts extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $title = '';
    
    public Part $part;
    public bool $parents = false;
    public bool $official = false;

    #[On ('subparts-updated')]
    public function searchUpdated() {
        $this->resetTable();
        $this->render();
    }

    public function table(Table $table): Table
    {

        return $table
            ->relationship(
                function () {
                    if ($this->parents == true) {
                        if ($this->official == true) {
                            return $this->part->parents()->official();
                        }  else {
                            return $this->part->parents()->unofficial();
                        }
                    } else {
                        if ($this->official == true) {
                            return $this->part->subparts()->official();
                        }  else {
                            return $this->part->subparts()->unofficial();
                        }
                    }    
                }
            )
            ->heading($this->title)
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->paginated(false)
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->state( 
                            fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                        )
                        ->extraImgAttributes(['class' => 'object-scale-down'])
                        ->grow(false),
                    TextColumn::make('filename')
                        ->description(fn (Part $p): string => $p->description)
//                        ->wrap()
                        ->label('Part')
                        ->grow(false),
                    ViewColumn::make('status')
                        ->view('tables.columns.part-status')
                        ->label('Status')
                        ->grow(false),
/*
                        TextColumn::make('download')
                        ->state( 
                            fn (Part $p): string => 
                                "<a href=\"" .
                                route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename) . 
                                "\">[DAT]</a>"
                        )
                        ->html()
                        ->grow(false)
*/
                ])
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
