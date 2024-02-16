<?php

namespace App\Livewire\Search;

use App\Models\Part;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class Parts extends Component implements HasForms
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
                Fieldset::make('Search Parameters')
                    ->schema([
                        TextInput::make('search')
                            ->nullable()
                            ->string(),
                        Select::make('scope')
                            ->options([
                                'filename' => 'Filename only',
                                'description' => 'Filename and description',
                                'header' => 'File header',
                                'file' => 'Entire file (very slow)'            
                            ])
                            ->default('header')
                            ->selectablePlaceholder(false)
                            ->native(false),
                    ]),
                Fieldset::make('Filters')
                    ->columns(3)
                    ->schema([
                        Section::make([
                            Select::make('user_id')
                                ->relationship(
                                    name: 'user',
                                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('realname', 'asc')
                                )
                                ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->realname} [{$u->name}]")
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->nullable(),
                            Split::make([
                                Toggle::make('exclude_user'),
                                Toggle::make('include_history'),    
                            ]),
                        ])
                            ->columnSpan(1),
                        Select::make('status')
                            ->options([
                                'certified' => 'Certified', 
                                'adminreview' => 'Needs Admin Review', 
                                'memberreview' => 'Needs More Votes', 
                                'held' => 'Hold', 
                            ])
                            ->native(false)
                            ->nullable(),
                        Select::make('part_type_id')
                            ->relationship(name: 'type', titleAttribute: 'name')
                            ->multiple()
                            ->preload()
                            ->native(false)
                            ->nullable(),
                    ]),
            ])
            ->statePath('data')
            ->model(Part::class);
    }

    public function doSearch(): void
    {
        $this->form->getState();
        $this->dispatch('search-updated');
    }
 
    public function render(): View
    {
        return view('livewire.search.parts')->layout('components.layout.tracker');
    }
}