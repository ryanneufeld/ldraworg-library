<?php

namespace App\Livewire\Part;

use App\Events\PartSubmitted;
use App\Jobs\UpdateZip;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\User;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Submit extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public array $part_errors = [];

    public array $submitted_parts = [];

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
                    ->maxFiles(15)
                    ->storeFiles(false)
                    ->required()
                    ->live()
                    ->label('Files')
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                            // Check if the fileformat is text or png
                            $detector = new \League\MimeTypeDetection\FinfoMimeTypeDetector;
                            $mimeType = $detector->detectMimeTypeFromPath($value->getPath().'/'.$value->getFilename()) ?: 'text/plain';
                            if ($mimeType != 'text/plain' && $mimeType != 'image/png') {
                                $this->part_errors[] = "{$value->getClientOriginalName()}: Incorrect file type";
                                $fail('File errors');

                                return;
                            }

                            // Error check based on file type
                            if ($mimeType == 'text/plain') {
                                $part = app(\App\LDraw\Parse\Parser::class)->parse($value->get());
                                $pparts = Part::query()->name($part->name ?? '')->get();
                                $unofficial_exists = $pparts->unofficial()->count() > 0;
                                $official_exists = $pparts->official()->count() > 0;
                                $errors = app(\App\LDraw\Check\PartChecker::class)->check($part, $value->getClientOriginalName());

                                // A part in the p and parts folder cannot have the same name
                                if (! is_null($pparts) && ! is_null($part->type) && ! is_null($part->name) &&
                                    $pparts->where('filename', "p/{$part->name}")->count() > 0 &&
                                    ($part->type == 'Part' || $part->type == 'Shortcut')) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: ".__('duplicate', ['type' => 'Primitive']);
                                } elseif (! is_null($pparts) && ! is_null($part->type) && ! is_null($part->name) &&
                                    $pparts->where('filename', "parts/{$part->name}")->count() > 0 &&
                                    $part->type == 'Primitive') {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: ".__('duplicate', ['type' => 'Parts']);
                                }

                                foreach ($errors ?? [] as $error) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: {$error}";
                                }
                            } elseif ($mimeType == 'image/png') {
                                $filename = $value->getClientOriginalName();
                                $unofficial_exists = ! is_null(Part::unofficial()->where('filename', 'LIKE', "%{$filename}")->first());
                                $official_exists = ! is_null(Part::official()->where('filename', 'LIKE', "%{$filename}")->first());
                            }

                            // Check if the part already exists on the tracker
                            if ($unofficial_exists && $get('replace') !== true) {
                                $this->part_errors[] = "{$value->getClientOriginalName()}: ".__('partcheck.replace');
                            }

                            if ($official_exists && ! $unofficial_exists && $get('official_fix') !== true) {
                                $this->part_errors[] = "{$value->getClientOriginalName()}: You must select Official File Fix to submit official part fixes";
                            }

                            if (count($this->part_errors) > 0) {
                                $fail('File errors');
                            }
                        },
                    ]),
                Toggle::make('replace')
                    ->label('Replace existing file(s)'),
                Toggle::make('official_fix')
                    ->label('Official File Fix'),
                Select::make('user_id')
                    ->relationship(name: 'user')
                    ->getOptionLabelFromRecordUsing(fn (User $u) => "{$u->realname} [{$u->name}]")
                    ->searchable()
                    ->preload()
                    ->default(Auth::user()->id)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->label('Proxy User')
                    ->visible(Auth::user()->can('part.submit.proxy')),
                Textarea::make('comments')
                    ->rows(5)
                    ->nullable()
                    ->string(),
            ])
            ->statePath('data')
            ->model(Part::class);
    }

    public function create(): void
    {
        $this->authorize('create', Part::class);
        $manager = app(PartManager::class);
        $this->part_errors = [];
        $data = $this->form->getState();
        if (array_key_exists('user_id', $data) && Auth::user()->can('part.submit.proxy')) {
            $user = User::find($data['user_id']);
        } else {
            $user = Auth::user();
        }
        $files = [];
        foreach ($data['partfiles'] as $file) {
            $detector = new \League\MimeTypeDetection\FinfoMimeTypeDetector;
            $mimeType = $detector->detectMimeTypeFromPath($file->getPath().'/'.$file->getFilename()) ?: 'text/plain';
            if ($mimeType == 'text/plain') {
                $files[] = ['type' => 'text', 'filename' => $file->getClientOriginalName(), 'contents' => $file->get()];
            } elseif ($mimeType == 'image/png') {
                $files[] = ['type' => 'image', 'filename' => $file->getClientOriginalName(), 'contents' => $file->get()];
            }
        }
        $parts = $manager->submit($files, $user);

        $parts->each(function (Part $p) use ($user, $data) {
            $user->notification_parts()->syncWithoutDetaching([$p->id]);
            UpdateZip::dispatch($p);
            PartSubmitted::dispatch($p, $user, $data['comments']);
            $this->submitted_parts[] = [
                'image' => version('images/library/unofficial/'.substr($p->filename, 0, -4).'_thumb.png'),
                'description' => $p->description,
                'filename' => $p->filename,
                'route' => route('tracker.show', $p),
            ];
        });
        $data = $this->form->fill();
        $this->render();
        $this->dispatch('open-modal', id: 'post-submit');
    }

    public function postSubmit()
    {
        $this->submitted_parts = [];
        $this->dispatch('close-modal', id: 'post-submit');
    }

    #[Layout('components.layout.tracker')]
    public function render(): View
    {
        return view('livewire.part.submit');
    }
}
