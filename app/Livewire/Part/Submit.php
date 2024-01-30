<?php

namespace App\Livewire\Part;

use App\Models\Part;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class Submit extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('partfiles')
                    ->multiple()
                    ->preserveFilenames()
                    ->minFiles(1)
                    ->maxFiles(15)
                    ->storeFiles(false)
                    ->required(),
                CheckboxList::make('options')
                    ->options([
                        'replace' => 'Replace existing file(s)',
                        'officialfix' => 'New version of official file(s)',
                    ]),
                Select::make('user_id')
                    ->relationship(name: 'user')
                    ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->realname} [{$u->name}]")
                    ->searchable()
                    ->preload()
                    ->default(Auth::user()->id)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->visible(Auth::user()->can('part.submit.proxy')),
                Textarea::make('comment')
                    ->rows(5)
            ])
            ->statePath('data')
            ->model(Part::class);
    }

    public function create(): void
    {
        dd($this->form->getState());
    }

    public function render(): View
    {
        return view('livewire.part.submit')->layout('components.layout.tracker');
    }
}