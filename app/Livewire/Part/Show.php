<?php

namespace App\Livewire\Part;

use App\Events\PartComment;
use App\Events\PartReviewed;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\Vote;
use App\Models\VoteType;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;

class Show extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

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
                                $v = $this->part->votes()->firstWhere('user_id', $u->id);
                                foreach(VoteType::orderBy('sort')->get() as $vt) {
                                    switch($vt->code) {
                                        case 'N':
                                            if (!is_null($v)) {
                                                $options[$vt->code] = $vt->name;
                                            }
                                            break;
                                        case 'M':
                                            if ($u->can('part.comment') || ($u->id == $this->part->user_id && $u()->can('part.own.comment'))) {
                                                $options[$vt->code] = $vt->name;
                                            }
                                            break;
                                        default:
                                            if ($u->can('part.vote.' . $vt->short) || ($u->id == $this->part->user_id && $u->can('part.own.vote.' . $vt->short))) {
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
    
    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn (): HasMany => $this->part->votes())
            ->columns([
                TextColumn::make('user.name')
                    ->label('User'),
                TextColumn::make('type.name')
                    ->label('Vote')
            ])
            ->heading('Current Reviews:')
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->paginated(false)
            ->striped();
    }

    public function mount(Part $part)
    {
        $this->form->fill();
        $this->part = $part;
        $this->part->load('events', 'history');
        $this->part->events->load('part_event_type', 'user', 'part', 'vote_type');
        $this->image = 
            $part->isTexmap() ? route("{$part->libFolder()}.download", $part->filename) : version("images/library/{$part->libFolder()}/" . substr($part->filename, 0, -4) . '.png');
    }

    public function deletePart(): void
    {
        if (
            !$this->part->isUnofficial() ||
            (is_null($this->part->official_part_id) && $this->part->parents->count() > 0) ||
            !Auth::check() || 
            Auth::user()->cannot('part.delete')
        ) {
            $this->dispatch('close-modal', ['id' => 'delete-part']);
            return;
        }
        $this->part->delete();
        $this->redirectRoute('tracker.activity');
    }

    public function updateImage(): void
    {
        $this->authorize('update', $this->part);
        app(PartManager::class)->updatePartImage($this->part);
        session()->flash('status', 'Image updated');
        $this->redirectRoute(($this->part->isUnofficial() ? 'tracker' : 'official') . '.show', [$this->part]);
    }
    
    public function updateSubparts(): void 
    {
        $this->authorize('update', $this->part);
        app(PartManager::class)->loadSubpartsFromBody($this->part);
        $this->redirectRoute(($this->part->isUnofficial() ? 'tracker' : 'official') . '.show', [$this->part]);
    }

    public function postVote() {
        if (!$this->part->isUnofficial() || !Auth::check()) {
            return;
        }
        $u = Auth::user();
        switch($this->vote_type_code) {
            case 'N':
                if (is_null($this->part->votes()->firstWhere('user_id', $u->id))) {
                    return;
                }
                Auth::user()->cancelVote($this->part);
                PartReviewed::dispatch($this->part, Auth::user(), null, $this->comment ?? null);
                break;
            case 'M':
                if (! ($u->can('part.comment') || ($u->id == $this->part->user_id && $u()->can('part.own.comment')))) {
                    return;
                }
                PartComment::dispatch($this->part, Auth::user(), $this->comment ?? null);
                break;
            default:
                $vt = VoteType::find($this->vote_type_code);
                if (is_null($vt) || !($u->can('part.vote.' . $vt->short) || ($u->id == $this->part->user_id && $u->can('part.own.vote.' . $vt->short)))) {
                    return;
                }    
                $u->castVote($this->part, $vt);
                PartReviewed::dispatch($this->part, $u, $this->vote_type_code, $this->comment ?? null);
        }
        $u->notification_parts()->syncWithoutDetaching([$this->part->id]);
        $this->form->fill();
    }

    public function toggleTracked()
    {
        if (Auth::check()) {
            Auth::user()->togglePartNotification($this->part);
        }
    }

    public function toggleDeleteFlag()
    {
        if (Auth::check() && Auth::user()->can('part.flag.delete')) {
            $this->part->delete_flag = !$this->part->delete_flag;
            $this->part->save();
        }
    }

    public function toggleManualHold()
    {
        if (Auth::check() && Auth::user()->can('part.flag.manual-hold')) {
            $this->part->manual_hold_flag = !$this->part->manual_hold_flag;
            $this->part->save();
        }
    }

    public function render()
    {
        return view('livewire.part.show')->layout('components.layout.tracker');
    }
}
