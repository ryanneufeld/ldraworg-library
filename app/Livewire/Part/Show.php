<?php

namespace App\Livewire\Part;

use App\Events\PartComment;
use App\Events\PartReviewed;
use App\Filament\Actions\Part\EditHeaderAction;
use App\Filament\Actions\Part\EditNumberAction;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\Vote;
use App\Models\VoteType;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
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
            EditHeaderAction::make('editHeader', $this->part)
        );
    }

    public function editNumberAction(): EditAction
    {
        return $this->menuAction(
            EditNumberAction::make('editNumber', $this->part)
        );
    }

    public function deleteAction(): DeleteAction
    {
        return $this->menuAction(
            DeleteAction::make('delete')
                ->record($this->part)
                ->visible(
                    $this->part->isUnofficial() &&
                    (!is_null($this->part->official_part) || $this->part->parents->count() === 0) &&
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
                ->label('Retie part fix')
                ->action(function() {
                    if ($this->part->isUnofficial()) {
                        $fixpart = Part::official()->firstWhere('filename', $this->part->filename);
                        $fixpart->unofficial_part()->associate($this->part);
                        $fixpart->save();
                    } else {
                        $fixpart = Part::unofficial()->firstWhere('filename', $this->part->filename);
                        $this->part->unofficial_part()->associate($fixpart);
                        $this->part->save();
                    }
                    $this->part->refresh();
                })
                ->visible(function (): bool {
                    if (!Auth::check() || 
                        Auth::user()?->cannot('update', $this->part) || 
                        Part::where('filename', $this->part->filename)->count() <= 1
                    ) {
                        return false;
                    }
                    return is_null($this->part->unofficial_part) && is_null($this->part->official_part);
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

    public function downloadZipAction(): Action 
    {
        return $this->menuAction(
            Action::make('zipdownload')
                ->label('Download zip file')
                ->url(fn() => route('unofficial.download.zip', str_replace('.dat', '.zip', $this->part->filename)))
                ->visible($this->part->isUnofficial() && 
                    $this->part->type->folder == 'parts/'
                )
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
        if ((!is_null($v) && $v->vote_type_code === 'A' &&  $this->vote_type_code === 'N') || $this->vote_type_code === 'A') {
            foreach($this->part->parentsAndSelf as $p) {
                app(PartManager::class)->checkPart($p);
            }
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
                    return route('official.show', $this->part->official_part->id ?? 0);
                }
                return route('tracker.show', $this->part->unofficial_part->id ?? 0);
            })
            ->visible(!is_null($this->part->unofficial_part) || !is_null($this->part->official_part));
    }

    #[Layout('components.layout.tracker')]
    public function render()
    {
        return view('livewire.part.show');
    }
}
