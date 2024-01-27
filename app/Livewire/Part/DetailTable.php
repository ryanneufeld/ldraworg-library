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
use Filament\Tables\Table as Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class DetailTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $title = '';
    
    public Part $part;
    public bool $parents = false;
    public bool $official = false;

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
                ImageColumn::make('image')
                    ->state( 
                        fn (Part $p): string => asset("images/library/{$p->libFolder()}" . substr($p->filename, 0, -4) . '_thumb.png')
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('filename')
                    ->description(fn (Part $p): string => $p->description)
                    ->wrap()
                    ->label('Part'),
                TextColumn::make('download')
                    ->state( 
                        fn (Part $p): string => 
                            "<a href=\"" .
                            route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename) . 
                            "\">[DAT]</a>"
                    )
                    ->html(),
                ViewColumn::make('status')
                    ->view('tables.columns.part-status')
                    ->label('Status'),
                
            ])
            ->recordUrl(
                fn (Part $p): string => 
                    route($p->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $p])
            )
            ->striped();
    }

    public function render(): View
    {
        return view('livewire.part.detail-table');
    }
}
