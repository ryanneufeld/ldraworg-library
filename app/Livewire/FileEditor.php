<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Layout;
use Livewire\Component;

class FileEditor extends Component implements HasForms
{
    use InteractsWithForms;

    public ?string $filepath = null;
    public string $text = '';

    protected array $dir_whitelist = [
        '/config',
        '/app',
        '/resources',
        '/database',
        '/storage',
        '/routes',
        '/lang',
        '/tests',
    ];
        
    protected array $ext_whitelist = [
        'php',
        'js',
        'css',
        'html',
        'htm',
        'log',
        'txt',
        'json',
    ];
        
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('filepath')
                    ->label('File Path')
                    ->required()
                    ->string()
            ]);
    }

    public function getFile()
    {
        $this->form->getState();
        $path = pathinfo($this->filepath);
        $isWhitelist = false;
        foreach ($this->dir_whitelist as $dir) {
            if (str_starts_with($path['dirname'], $dir) && in_array($path['extension'], $this->ext_whitelist)) {
                $isWhitelist = true;
            }
        }
        if (str_ends_with($path['filename'], '.blade') && $path['extension'] == 'php') {
            $mode = 'php_laravel_blade';
        } else {
            switch($path['extension']) {
                case 'js':
                    $mode = 'javascript';
                    break;
                case 'htm':
                    $mode = 'html';
                    break;
                case 'txt':
                case 'log':
                    $mode = 'text';
                    break;
                default:
                    $mode = $path['extension'];
            }
        }
        if (file_exists(base_path($this->filepath)) &&
            $isWhitelist == true &&
            auth()->user()->can('edit-files')
        ) {
            $contents = file_get_contents(base_path($this->filepath));
            $this->dispatch('file-loaded', contents: $contents, mode: $mode);
        } else {
            $this->dispatch('file-loaded', contents: '', mode: 'text');
        }
    }

    public function saveFile(string $contents)
    {
        if (file_exists(base_path($this->filepath)) &&
            auth()->user()->can('edit-files')
        ) {
            file_put_contents(base_path($this->filepath), $contents);
        }
    }
    #[Layout('components.layout.base')]
    public function render()
    {
        return view('livewire.file-editor');
    }
}
