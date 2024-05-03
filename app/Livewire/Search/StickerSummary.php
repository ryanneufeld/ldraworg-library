<?php

namespace App\Livewire\Search;

use App\Models\Part;
use App\Models\StickerSheet;
use Filament\Forms\Components\Select;
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

    public ?string $sheet = null;

    public ?Collection $parts = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('sheet')
                    ->options(fn(): array =>
                        StickerSheet::all()->mapWithKeys(function (StickerSheet $s, int $key) {
                            if (is_null($s->rebrickable_part)) {
                                return [$s->number => "Sticker Sheet {$s->number}"];
                            }
                            return [$s->number => "{$s->rebrickable_part->name} ({$s->number})"];
                        })->all()
                    )
                    ->searchable()
                    ->required()
                    ->live()
            ]);
    }

    public function doSearch()
    {
        $this->form->getState();
        $this->parts = Part::where(fn (Builder $q) =>
            $q->orWhere(fn (Builder $qu) =>
                $qu->where('filename', 'LIKE', "parts/{$this->sheet}%.dat")->whereRelation('category', 'category', 'Sticker')
            )->orWhereHas('subparts', fn (Builder $qu) =>
                $qu->where('filename', 'LIKE', "parts/{$this->sheet}%.dat")->whereRelation('category', 'category', 'Sticker')
            )
        )->whereRelation('type', 'folder', 'parts/')->orderBy('filename', 'asc')->get();    
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.search.sticker-summary');
    }
}
