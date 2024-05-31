<?php

namespace App\Livewire\Search;

use App\LDraw\PartRepository;
use App\Models\Part;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class Suffix extends Component implements HasForms
{
    use InteractsWithForms;

    public string $activeTab = 'patterns';

    #[Url]
    public ?string $basepart = null;

    public function mount(): void
    {
        $this->form->fill(['basepart' => $this->basepart]);
        if (!is_null($this->basepart)) {
            $this->doSearch();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('basepart')
                    ->label('Base Part Number')
                    ->required()
                    ->string(),
            ]);
    }

    #[Computed(persist: true)]
    public function part()
    {
        $bp = $this->basepart;
        if (!str_ends_with($this->basepart, '.dat')) {
            $bp .= '.dat';
        }
        return Part::firstWhere('filename', "parts/{$bp}");
    }

    #[Computed(persist: true)]
    public function patterns()
    {
        if (is_null($this->part)) {
            return new Collection();
        }
        $pat = (new PartRepository())->patternParts($this->part);
        $pat->load(['votes', 'official_part']);
        return $pat;
    }

    #[Computed(persist: true)]
    public function composites()
    {
        if (is_null($this->part)) {
            return new Collection();
        }
        $com = (new PartRepository())->compositeParts($this->part);
        $com->load(['votes', 'official_part']);
        return $com;
    }

    #[Computed]
    public function shortcuts()
    {
        if (is_null($this->part)) {
            return new Collection();
        }
        $st = (new PartRepository())->stickerShortcutParts($this->part);
        $st->load(['votes', 'official_part']);
        return $st;
    }

    public function doSearch()
    {
        $this->form->getState();
        unset($this->patterns);
        unset($this->composites);
        unset($this->part);
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.search.suffix');
    }
}
