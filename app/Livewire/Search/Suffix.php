<?php

namespace App\Livewire\Search;

use App\Models\Part;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Suffix extends Component implements HasForms
{
    use InteractsWithForms;

    public string $activeTab = 'patterns';
    public string $basepart = '';
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('search')
                    ->label('Base Part Number')
                    ->required()
                    ->string(),
            ])
            ->statePath('data');
    }

    public function doSearch()
    {
        $this->form->getState();
        $filename = "parts/{$this->data['search']}";
        if (strpos($filename, '.dat') === false) {
            $filename .= '.dat';
        }

        $part = Part::official()->firstWhere('filename', $filename) ?? Part::unofficial()->firstWhere('filename', $filename);
        $this->basepart = is_null($part) ? '' : $part->basePart();
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        $offset = $this->basepart !== '' ? strlen($this->basepart) + 1 : 0;
        $patterns = Part::patterns($this->basepart)->orderBy('filename')->get()->groupBy(fn(Part $item, int $key) => $item->name()[$offset]);
        $composites = Part::composites($this->basepart)->orderBy('filename')->get();
        $shortcuts = Part::stickerShortcuts($this->basepart)->orderBy('filename')->get();
        return view('livewire.search.suffix', compact('patterns', 'composites', 'shortcuts'));
    }
}
