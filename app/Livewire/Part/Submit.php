<?php

namespace App\Livewire\Part;

use App\Events\PartSubmitted;
use App\Jobs\UpdateZip;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\PartType;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

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
                            if ($value->getMimeType() == 'text/plain') {
                                $part = app(\App\LDraw\Parse\Parser::class)->parse($value->get());
                                $official_exists = !is_null(Part::official()->name($part->name)->first());
                                $unofficial_exists = !is_null(Part::unofficial()->name($part->name)->first());
                                $part = app(\App\LDraw\Parse\Parser::class)->parse($value->get());
                                $errors = app(\App\LDraw\Check\PartChecker::class)->check($part);
                                foreach($errors ?? [] as $error) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: {$error}";
                                    //$this->addError($value->getClientOriginalName(), $error);
                                }
                            } elseif ($value->getMimeType() == 'image/png') {
                                $filename = $value->getClientOriginalName();
                                $official_exists = !is_null(Part::official()->where('filename', 'LIKE', "%{$filename}")->first());
                                $unofficial_exists = !is_null(Part::unofficial()->where('filename', 'LIKE', "%{$filename}")->first());
                            }
                            else {
                                $part_errors[] = "{$value->getClientOriginalName()}: Incorrect file type";
                            }
                            if ($value->getMimeType() == 'text/plain' || $value->getMimeType() == 'image/png')
                            {
                                $cannotfix = !Auth::check() || Auth::user()->cannot('part.submit.fix');
                                if ($official_exists && !$unofficial_exists && $cannotfix) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: " . __('partcheck.fix.unofficial');
                                }
                                elseif ($official_exists && !$unofficial_exists && !in_array('officialfix', $get('options'))) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: " . __('partcheck.fix.checked');
                                }  
                                if ($unofficial_exists && !in_array('replace', $get('options'))) {
                                    $this->part_errors[] = "{$value->getClientOriginalName()}: " . __('partcheck.replace');
                                }
                            }
                            if (count($this->part_errors) > 0) {
                                $fail('File errors');
                            }  
                        },
                    ]),
                CheckboxList::make('options')
                    ->options(function () {
                            $options = ['replace' => 'Replace existing file(s)'];
                            if (Auth::user()->can('part.submit.fix')) {
                                $options['officialfix'] = 'New version of official file(s)';
                            }
                            return $options;
                        }),
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
                    ->string()
            ])
            ->statePath('data')
            ->model(Part::class);
    }

    public function create(): void
    {
        $this->authorize('create', Part::class);
        $manager = app(PartManager::class);;
        $this->part_errors = [];
        $data = $this->form->getState();
        if (!is_null($data['user_id']) && Auth::user()->can('part.submit.proxy')) {
            $user = User::find($data['user_id']);
        } else {
            $user = Auth::user();
        }
        $parts = new Collection();
        foreach($data['partfiles'] as $file) {
            if ($file->getMimeType() == 'text/plain') {
                $part = $manager->addOrChangePartFromText($file->get());
            } else {
                $image = imagecreatefrompng($file->path());
                imagesavealpha($image, true);
                $part = $manager->addOrChangePartFromImage(
                    $file->path(),
                    basename($file->getClientOriginalName()),
                    $user,
                    $this->guessPartType($file->getClientOriginalName(), $data['partfiles'])
                );
            }
            $user->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);
            PartSubmitted::dispatch($part, $user, $data['comments']);
            $parts->add($part);
        }
        $parts->each(function (Part $p) use ($manager) {
            $manager->loadSubpartsFromBody($p);
            $this->submitted_parts[] = [
                'image' => version("images/library/unofficial/" . substr($p->filename, 0, -4) . '_thumb.png'),
                'description' => $p->description,
                'filename' => $p->filename,
                'route' => route('tracker.show', $p)
            ];
        });
        $data = $this->form->fill();
        $this->render();
        $this->dispatch('open-modal', id: 'post-submit');
    }
 
    protected function guessPartType(string $filename, array $partfiles): PartType
    {
        $p = Part::firstWhere('filename', 'LIKE', "%{$filename}");
        //Texmap exists, use that type
        if (!is_null($p)) {
            return $p->type;
        }
        // Texmap is used in one of the submitted files, use the type appropriate for that part
        foreach ($partfiles as $file) {
            if ($file->getMimeType() == 'text/plain' && stripos($filename, $file->get() !== false)) {
                $type = $this->manager->parser->parse($file->get())->type;
                $pt = PartType::firstWhere('type', $type);
                $textype = PartType::firstWhere('type', "{$pt->type}_Texmap");
                if (!is_null($textype)) {
                    return $textype;
                }
            }
        }
        return PartType::firstWhere('type', 'Part_Texmap');
    }

    public function postSubmit()
    {
        $this->submitted_parts = [];
        $this->dispatch('close-modal', id: 'post-submit');
    }

    public function render(): View
    {
        return view('livewire.part.submit')->layout('components.layout.tracker');
    }
}