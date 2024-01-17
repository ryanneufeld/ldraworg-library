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
use Filament\Tables\Table as FilamentTable;
use Filament\Support\Markdown;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Graph\Collection as GraphCollection;

class Table extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public GraphCollection|Collection $parts;
    public string $title = '';
    
    public function table(FilamentTable $table): FilamentTable
    {
        return $table
            ->query($this->parts->count() < 1 ? Part::whereNull('id') : $this->parts->toQuery())
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
            ->striped();
    }

    public function render(): View
    {
        return view('livewire.part.table');
    }
}
