<?php

namespace App\Livewire\Search;

use App\Models\Part;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class StickerSummary extends Component implements HasForms
{
    use InteractsWithForms;

    #[Url]
    public ?string $search = '';
    
    public ?Collection $parts = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Sticker Number')
                    ->required()
                    ->string(),
            ]);
    }

    public function doSearch()
    {
        $this->form->getState();
        $this->parts = Part::where(fn (Builder $q) =>
            $q->orWhere(fn (Builder $qu) =>
                $qu->where('filename', 'LIKE', "parts/{$this->search}%.dat")->whereRelation('category', 'category', 'Sticker')
            )->orWhereHas('subparts', fn (Builder $qu) =>
                $qu->where('filename', 'LIKE', "parts/{$this->search}%.dat")->whereRelation('category', 'category', 'Sticker')
            )
        )->whereRelation('type', 'folder', 'parts/')->orderBy('filename', 'asc')->get();    
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.search.sticker-summary');
    }
}
