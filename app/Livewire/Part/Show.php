<?php

namespace App\Livewire\Part;

use App\Events\PartComment;
use App\Events\PartHeaderEdited;
use App\Events\PartRenamed;
use App\Events\PartReviewed;
use App\Events\PartSubmitted;
use App\Jobs\UpdateZip;
use App\LDraw\Parse\Parser;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartHistory;
use App\Models\PartType;
use App\Models\PartTypeQualifier;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteType;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public Part $part;
    public ?string $comment;
    public ?string $vote_type_code;
    public string $image;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Comment / Vote')
                    ->schema([
                        Radio::make('vote_type_code')
                            ->options(function () {
                                if (!Auth::check()) return [];
                                $options = [];                                
                                $u = Auth::user();
                                $v = $this->part->votes->firstWhere('user_id', $u->id);
                                foreach(VoteType::orderBy('sort')->get() as $vt) {
                                    switch($vt->code) {
                                        case 'N':
                                            if (!is_null($v) && $u->can('update', [$v, $vt->code])) {
                                                $options[$vt->code] = $vt->name;
                                            }
                                            break;
                                        default:
                                            if (
                                                (is_null($v) && $u->can('create', [Vote::class, $this->part, $vt->code])) ||
                                                $u->can('update', [$v, $vt->code])
                                            ) {
                                                if (is_null($v) || $v->vote_type_code != $vt->code )
                                                $options[$vt->code] = $vt->name;
                                            }    
                                    }
                                }
                                return $options;
                            })
                            ->default('M')
                            ->required()
                            ->markAsRequired(false)
                            ->in(array_keys(VoteType::orderBy('sort')->pluck('name', 'code')->all()))
                            ->inline()
                            ->inlineLabel(false)     
                            ->disableOptionWhen(fn (string $value): bool => $value === 'published')
                            ->live(),
                        Textarea::make('comment')
                            ->rows(5)
                            ->string()
                            ->nullable()
                            ->required(fn (Get $get): bool => in_array($get('vote_type_code'), ['M', 'H'])),
                ])    
            ])
            ->model(Vote::class);
    }
    
    public function mount(?Part $part, ?Part $unofficialpart, ?Part $officialpart)
    {
        if ($part->exists) {
            $this->part = $part;
        } elseif ($unofficialpart->exists) {
            $this->part = $unofficialpart;
        } elseif ($officialpart->exists) {
            $this->part = $officialpart;
        } else {
            return response(404);
        }
        
        $this->part->load('events', 'history');
        $this->part->events->load('part_event_type', 'user', 'part', 'vote_type');
        $this->image = 
            $this->part->isTexmap() ? route("{$this->part->libFolder()}.download", $this->part->filename) : version("images/library/{$this->part->libFolder()}/" . substr($this->part->filename, 0, -4) . '.png');
        $this->form->fill();
    }

    protected function menuAction(Action $a): Action 
    {
        return $a
            ->link()
            ->color('gray')
            ->extraAttributes([
                'class' => 'p-2 hover:bg-gray-300',
            ]);
    }

    public function editHeaderAction(): EditAction
    {
        return $this->menuAction(
            EditAction::make('editHeader')
                ->label('Edit Header')
                ->record($this->part)
                ->form([
                    TextInput::make('description')
                        ->required()
                        ->string()
                        ->rules([
                            fn (): Closure => function (string $attribute, mixed $value, Closure $fail)
                            {
                                if (! app(\App\LDraw\Check\PartChecker::class)->checkLibraryApprovedDescription($value)) {
                                    $fail('partcheck.description.invalidchars')->translate();
                                }
                                if (
                                    $this->part->type->folder == 'parts/' && 
                                    ! app(\App\LDraw\Check\PartChecker::class)->checkDescriptionForPatternText($this->part->name(), $value)
                                ) {
                                    $fail('partcheck.description.patternword')->translate();
                                }
                            }
                        ]),
                    Select::make('part_type_id')
                        ->relationship(
                            name: 'type',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query->where('folder', 'parts/'),
                        )
                        ->hidden($this->part->type->folder !== 'parts/')
                        ->disabled($this->part->type->folder !== 'parts/')
                        ->selectablePlaceholder(false)
                        ->native(false),
                    Select::make('part_type_qualifier_id')
                        ->relationship(
                            name: 'type_qualifier',
                            titleAttribute: 'name',
                        )
                        ->nullable()
                        ->hidden($this->part->type->folder !== 'parts/')
                        ->disabled($this->part->type->folder !== 'parts/')
                        ->native(false),
                    TextArea::make('help')
                        ->helperText('Do not include 0 !HELP; each line will be a separate help line')
                        ->extraAttributes(['class' => 'font-mono'])
                        ->rows(6)
                        ->nullable()
                        ->string(),
                    Select::make('part_category_id')
                        ->relationship(
                            name: 'category',
                            titleAttribute: 'category',
                        )
                        ->helperText('A !CATEGORY meta will be added only if this differs from the first word in the description')
                        ->hidden($this->part->type->folder !== 'parts/')
                        ->disabled($this->part->type->folder !== 'parts/')
                        ->selectablePlaceholder(false)
                        ->native(false)
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get)
                            {
                                if($this->part->type->folder == 'parts/') {
                                    $c = app(\App\LDraw\Parse\Parser::class)->getDescriptionCategory($get('description'));
                                    $cat = PartCategory::firstWhere('category', $c);
                                    if (is_null($cat) && is_null($value)) {
                                        $fail('partcheck.category.invalid')->translate(['value' => $c]);
                                    } 
                                }
                            }
                        ]),
                    TextArea::make('keywords')
                        ->helperText('Do not include 0 !KEYWORDS; the number of keyword lines and keyword order will not be preserved')
                        ->extraAttributes(['class' => 'font-mono'])
                        ->rows(3)
                        ->nullable()
                        ->string()
                        ->rules([
                            fn (): Closure => function (string $attribute, mixed $value, Closure $fail)
                            {
                                $keywords = "0 !KEYWORDS " . str_replace(["\n","\r"], [', ',''], $value);
                                $keywords = app(\App\LDraw\Parse\Parser::class)->getKeywords($keywords) ?? [];
                                if (
                                    $this->part->type->folder == 'parts/' && 
                                    ! app(\App\LDraw\Check\PartChecker::class)->checkPatternForSetKeyword($this->part->name(), $keywords)
                                ) {
                                    $fail('partcheck.keywords')->translate();
                                }
                            }
                        ]),
                    TextInput::make('cmdline')
                        ->nullable()
                        ->string(),
                    TextArea::make('history')
                        ->helperText('Must include 0 !HISTORY; ALL changes to existing history must be documented with a comment')
                        ->extraAttributes(['class' => 'font-mono'])
                        ->rows(6)
                        ->nullable()
                        ->string()
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get)
                            {
                                $value = Parser::dos2unix(trim($value));
                                if (!is_null($value)) {
                                    $lines = explode("\n", $value);
                                    if ($value !== '' && count($lines) != mb_substr_count($value, '0 !HISTORY')) {
                                        $fail('partcheck.history.invalid')->translate();
                                        return;
                                    }  
                        
                                    $history = app(\App\LDraw\Parse\Parser::class)->getHistory($value);
                                    if (! is_null($history)) {
                                        foreach ($history as $hist) {
                                            if (is_null(User::fromAuthor($hist['user'])->first())) {
                                                $fail('partcheck.history.author')->translate();
                                            }
                                        }
                                    }
                                }
                                                        
                                $hist = '';
                                foreach ($this->part->history()->oldest()->get() as $h) {
                                    $hist .= $h->toString() . "\n";
                                }
                                $hist = Parser::dos2unix(trim($hist));
                                if (((!empty($hist) && empty($value)) || $hist !== $value) && empty($get('editcomment'))) {
                                    $fail('partcheck.history.alter')->translate();
                                }
                            }                    
                        ]),
                    TextArea::make('editcomment')
                        ->label('Comment')
                        ->extraAttributes(['class' => 'font-mono'])
                        ->rows(3)
                        ->nullable()
                        ->string()
                ])
                ->mutateRecordDataUsing(function (array $data): array {
                    $data['help'] = $this->part->help()->orderBy('order')->get()->implode('text', "\n");
                    $data['keywords'] = $this->part->keywords()->orderBy('keyword')->get()->implode('keyword', ", ");
                    $data['history'] = '';
                    foreach($this->part->history as $h) {
                        $data['history'] .= $h->toString() . "\n";
                    }
                    return $data;
                })
                ->using(fn(Part $p, array $data) => $this->updateHeader($p, $data))
                ->successNotificationTitle('Header updated')
                ->visible(Auth::user()?->can('update', $this->part) ?? false)
        );
    }

    protected function updateHeader(Part $part, array $data): Part
    {
        $manager = app(PartManager::class);
        $changes = ['old' => [], 'new' => []];
        if ($data['description'] !== $part->description) {
            $changes['old']['description'] = $part->description;
            $changes['new']['description'] = $data['description'];
            $part->description = $data['description'];
            if ($part->type->folder === 'parts/') {
                $cat = $manager->parser->getDescriptionCategory($part->description);
                $cat = PartCategory::firstWhere('category', $cat);
                if (!is_null($cat) && $part->part_category_id !== $cat->id) {
                    $part->part_category_id = $cat->id;
                }    
            }
        }

        if ($part->type->folder === 'parts/' && 
            !is_null($data['part_category_id']) && 
            $part->part_category_id !== (int)$data['part_category_id']
        ) {
            $cat = PartCategory::find($data['part_category_id']);
            $changes['old']['category'] = $part->category->category;
            $changes['new']['category'] = $cat->category;
            $part->part_category_id = $cat->id;
        }

        if ($part->type->folder === 'parts/' && (int)$data['part_type_id'] !== $part->part_type_id) {
            $pt = PartType::find($data['part_type_id']);
            $changes['old']['type'] = $part->type->type;
            $changes['new']['type'] = $pt->type;
            $part->part_type_id = $pt->id;
        }
        
        if (!is_null($data['part_type_qualifier_id'] ?? null)) {
            $pq = PartTypeQualifier::find($data['part_type_qualifier_id']);
        } else {
            $pq = null;
        }
        if ($part->part_type_qualifier_id !== ($pq->id ?? null)) {
            $changes['old']['qual'] = $part->type_qualifier->type ?? '';
            $changes['new']['qual'] = $pq->type ?? '';
            $part->part_type_qualifier_id = $pq->id ?? null;
        }

        if (!is_null($data['help'] ?? null) && trim($data['help']) !== '') {
            $newHelp = "0 !HELP " . str_replace(["\n","\r"], ["\n0 !HELP ",''], $data['help']);
            $newHelp = $manager->parser->getHelp($newHelp);
        } else {
            $newHelp = [];
        }

        $partHelp = $part->help->pluck('text')->all();
        if ($partHelp !== $newHelp) {
            $changes['old']['help'] = "0 !HELP " . implode("\n0 !HELP ", $partHelp);
            $changes['new']['help'] = "0 !HELP " . implode("\n0 !HELP ", $newHelp);
            $part->setHelp($newHelp);    
        }

        if (!is_null($data['keywords'] ?? null)) {
            $newKeywords = '0 !KEYWORDS ' . str_replace(["\n","\r"], [', ',''], $data['keywords']);
            $newKeywords = $manager->parser->getKeywords($newKeywords);
        } else {
            $newKeywords = [];
        }

        $partKeywords = $part->keywords->pluck('keyword')->all();
        if ($partKeywords !== $newKeywords) {
            $changes['old']['keywords'] = implode(", ", $partKeywords);
            $changes['new']['keywords'] = implode(", ", $newKeywords);
            $part->setKeywords($newKeywords);    
        }

        $newHistory = $manager->parser->getHistory($data['history'] ?? '');
        $partHistory = [];
        if ($part->history->count() > 0) {
            foreach($part->history as $h) {
                $partHistory[] = $h->toString();
            }
        }
        $partHistory = implode("\n", $partHistory);
        if ($manager->parser->getHistory($partHistory) !== $newHistory) {
            $changes['old']['history'] = $partHistory;
            $part->setHistory($newHistory);
            $part->refresh();    
            $changes['new']['history'] = '';
            if ($part->history->count() > 0) {
                foreach($part->history as $h) {
                    $changes['new']['history'] .= $h->toString() . "\n";
                }
            }
        }

        if ($part->cmdline !== ($data['cmdline'] ?? null)) {
            $changes['old']['cmdline'] = $part->cmdline ?? '';
            $changes['new']['cmdline'] = $data['cmdline'] ?? '';
            $part->cmdline = $data['cmdline'] ?? null;
            $partHistory = [];
            if ($part->history->count() > 0) {
                foreach($part->history as $h) {
                    $partHistory[] = $h->toString();
                }
            }
            $partHistory = implode("\n", $partHistory);
        }

        if (count($changes['new']) > 0) {
            $part->save();
            $part->refresh();
            $part->generateHeader();
            
            // Post an event
            PartHeaderEdited::dispatch($part, Auth::user(), $changes, $data['editcomment'] ?? null);
            Auth::user()->notification_parts()->syncWithoutDetaching([$part->id]);
            UpdateZip::dispatch($part);    
        }

        return $part;
    }

    public function editNumberAction(): EditAction
    {
        return $this->menuAction(
            EditAction::make('editNumber')
                ->label('Renumber/Move')
                ->modalHeading('Move/Renumber Part')
                ->record($this->part)
                ->form([
                    TextInput::make('folder')
                        ->label('Current Location')
                        ->placeholder($this->part->type->folder)
                        ->disabled(),
                    TextInput::make('name')
                        ->label('Current Name')
                        ->placeholder(basename($this->part->filename))
                        ->disabled(),
                    Radio::make('part_type_id')
                        ->label('Select destination folder:')
                        ->options(function(): array 
                        {
                            $options = [];
                            foreach (PartType::where('format', $this->part->type->format)->pluck('folder', 'id')->unique() as $id => $option) {
                                $types = implode(', ', PartType::where('folder', $option)->pluck('name')->all());
                                $options[$id] = "{$option} ({$types})"; 
                            }
                            return $options;                   
                        }),
                    TextInput::make('newname')
                        ->label('New Name')
                        ->helperText('Exclude the folder from the name')
                        ->nullable()
                        ->string()
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get)
                            {
                                if (!empty($get('part_type_id'))) {
                                    $newType = PartType::find($get('part_type_id'));
                                    $p = Part::find($this->part->id);
                                    if (!empty($newType) && !empty($p)) {
                                        $newName = basename($value, ".{$p->type->format}");
                                        $newName = "{$newType->folder}{$newName}.{$newType->format}";
                                        $oldp = Part::firstWhere('filename', $newName);
                                        if (!is_null($oldp))  {
                                            $fail($newName . " already exists");
                                        }          
                                    }    
                                }
                            }
                        ]),
                ])
                ->successNotificationTitle('Renumber/Move Successful')
                ->using(fn(Part $p, array $data) => $this->updateMove($p, $data))
                ->visible(Auth::user()?->can('move', $this->part) ?? false)
        );
    }

    protected function updateMove(Part $part, array $data): Part
    {
        $manager = app(PartManager::class);
        $newType = PartType::find($data['part_type_id']);
        $newName = basename($data['newname'], ".{$part->type->format}");
        $newName = "{$newName}.{$newType->format}";
        if ($part->isUnofficial()) {
            $oldname = $part->filename;
            $manager->movePart($part, $newName, $newType);
            $part->refresh();
            PartRenamed::dispatch($part, Auth::user(), $oldname, $part->filename);
        } else {
            $upart = Part::unofficial()->where('filename', "{$newType->folder}$newName")->first();
            if (is_null($upart)) {
                $upart = $manager->copyOfficialToUnofficialPart($part);
                PartHistory::create([
                    'part_id' => $upart->id,
                    'user_id' => Auth::user()->id,
                    'comment' => 'Moved from ' . $part->name(),
                ]);
                $upart->refresh();
                $manager->movePart($upart, $newName, $newType);
                PartSubmitted::dispatch($upart, Auth::user());
            }
            $mpart = $manager->addMovedTo($part, $upart);
            $mpart->official_part->associate($part);
            $part->unofficial_part->associate($mpart);
            $part->save();
            $mpart->save();
            PartSubmitted::dispatch($mpart, Auth::user());
        }
        return $part;
    }

    public function deleteAction(): DeleteAction
    {
        return $this->menuAction(
            DeleteAction::make('delete')
                ->record($this->part)
                ->visible(
                    $this->part->isUnofficial() &&
                    (!is_null($this->part->official_part_id) || $this->part->parents->count() === 0) &&
                    Auth::user()?->can('delete', $this->part) ?? false
                )
                ->modalDescription('Are you sure you\'d like to delete this part? This cannot be easily undone.')
                ->successRedirectUrl(route('tracker.activity'))
                ->successNotificationTitle('Part deleted')
        );
    }

    public function updateImageAction(): Action
    {
        return $this->menuAction(
            Action::make('updateImage')
                ->action(function() {
                    app(PartManager::class)->updatePartImage($this->part);
                    $this->dispatch('subparts-updated');
                    Notification::make()
                        ->title('Image Updated')
                        ->success()
                        ->send();    
                })
                ->visible(Auth::user()?->can('update', $this->part) ?? false)
        );
    }
    
    public function updateSubpartsAction(): Action 
    {
        return $this->menuAction(
            Action::make('updateSubparts')
                ->action(function() {
                    app(PartManager::class)->loadSubpartsFromBody($this->part);
                    $this->dispatch('subparts-updated');
                    Notification::make()
                        ->title('Subparts Reloaded')
                        ->success()
                        ->send();    
                })
                ->visible(Auth::user()?->can('update', $this->part) ?? false)
        );
    }

    public function retieFixAction(): Action
    {
        return $this->menuAction(
            Action::make('retieFix')
                ->label('Retie official part')
                ->action(function() {
                    if ($this->part->isUnofficial()) {
                        $fixpart = Part::official()->firstWhere('filename', $this->part->filename);
                        $this->part->official_part()->associate($fixpart);
                    } else {
                        $fixpart = Part::unofficial()->firstWhere('filename', $this->part->filename);
                        $this->part->unofficial_part()->associate($fixpart);
                    }
                    $this->part->save();
                })
                ->visible(function (): bool {
                    if (!Auth::check() || 
                        Auth::user()?->cannot('retie', $this->part) || 
                        Part::where('filename', $this->part->filename)->count() > 1
                    ) {
                        return false;
                    }
                    if ($this->part->isUnofficial() && is_null($this->part->official_part_id)) {
                        return true;
                    }
                    if (!$this->part->isUnofficial() && is_null($this->part->unofficial_part_id)) {
                        return true;
                    }
                    return false;
                })
        );
    }
    public function downloadAction(): Action 
    {
        return $this->menuAction(
            Action::make('download')
                ->url(fn() => route($this->part->isUnofficial() ? 'unofficial.download' : 'official.download', $this->part->filename))
        );
    }

    public function webglViewAction(): Action 
    {
        return $this->menuAction(
            Action::make('webglView')
                ->label('3D View')
                ->action(fn() => $this->dispatch('open-modal', id: 'ldbi'))
        );
    }

    public function adminCertifyAllAction(): Action
    {
        return $this->menuAction(
            Action::make('adminCertifyAll')
                ->action(function () {
                    $this->authorize('admin', [Vote::class, $this->part]);
                    foreach ($this->part->descendantsAndSelf->where('vote_sort', 2) as $p) {
                        Auth::user()->castVote($p, VoteType::firstWhere('code', 'A'));
                        $p->updateVoteData();
                        PartReviewed::dispatch($p, Auth::user(), 'A', null);
                        Auth::user()->notification_parts()->syncWithoutDetaching([$p->id]);
                    }
                    Notification::make()
                    ->title('Quickvote action complete')
                    ->success()
                    ->send();            
                })
                ->visible(
                    $this->part->isUnofficial() && 
                    $this->part->type->folder == 'parts/' && 
                    $this->part->descendantsAndSelf->where('vote_sort', '>', 2)->count() == 0 &&
                    Auth::user()?->can('create', [Vote::class, $this->part,'A']) ?? false
                )
        );
    }

    public function postVote() {
        $u = Auth::user();        
        $v = $this->part->votes()->firstWhere('user_id', $u->id);
        if (is_null($v)) {
            $this->authorize('create', [Vote::class, $this->part, $this->vote_type_code]);
        } else {
            $this->authorize('update', [$v, $this->vote_type_code]);
        }

        switch($this->vote_type_code) {
            case 'N':
                $u->cancelVote($this->part);
                PartReviewed::dispatch($this->part, $u, null, $this->comment ?? null);
                break;
            case 'M':
                PartComment::dispatch($this->part, Auth::user(), $this->comment ?? null);
                break;
            default:
                $vt = VoteType::find($this->vote_type_code);
                $u->castVote($this->part, $vt);
                PartReviewed::dispatch($this->part, $u, $this->vote_type_code, $this->comment ?? null);
        }
        $u->notification_parts()->syncWithoutDetaching([$this->part->id]);
        $this->form->fill();
    }

    public function toggleTrackedAction(): Action
    {
        return Action::make('toggleTracked')
            ->button()
            ->color(Auth::user()?->notification_parts->contains($this->part->id) ? 'yellow' : 'gray')
            ->icon('fas-bell')
            ->label(Auth::user()?->notification_parts->contains($this->part->id) ? 'Tracking' : 'Track')
            ->action(function() {
                Auth::user()->notification_parts()->toggle([$this->part->id]);
            })
            ->visible(Auth::check());
    }

    public function toggleDeleteFlagAction(): Action
    {
        return Action::make('toggleDeleteFlag')
            ->button()
            ->color($this->part->delete_flag ? 'red' : 'gray')
            ->icon('fas-flag')
            ->label($this->part->delete_flag ? 'Flagged for Deletion' : 'Flag for Deletion')
            ->action(function() {
                $this->part->delete_flag = !$this->part->delete_flag;
                $this->part->save();
            })
            ->visible(Auth::user()?->can('flagDelete', $this->part) ?? false);
    }

    public function toggleManualHoldAction(): Action
    {
        return Action::make('toggleManualHold')
            ->button()
            ->color($this->part->manual_hold_flag ? 'red' : 'gray')
            ->icon('fas-flag')
            ->label($this->part->manual_hold_flag ? 'On Administrative Hold' : 'Place on Administrative Hold')
            ->action(function() {
                $this->part->manual_hold_flag = !$this->part->manual_hold_flag;
                $this->part->save();
            })
            ->visible(Auth::user()?->can('flagMaualHold', $this->part) ?? false);
    }

    public function viewFixAction(): Action
    {
        return Action::make('viewFix')
            ->button()
            ->color('gray')
            ->icon('fas-copy')
            ->label('View ' . ($this->part->isUnofficial() ? 'official' : 'unofficial')  . ' version of part')
            ->url(function () {
                if ($this->part->isUnofficial())
                {
                    return route('official.show', $this->part->official_part_id ?? 0);
                }
                return route('tracker.show', $this->part->unofficial_part_id ?? 0);
            })
            ->visible(function (): bool {
                if ($this->part->isUnofficial()) {
                    return !is_null($this->part->official_part_id);
                }
                return !is_null($this->part->unofficial_part_id);
            });
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.part.show');
    }
}
