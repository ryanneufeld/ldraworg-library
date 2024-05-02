<?php

namespace App\Livewire\Release;

use App\Jobs\MakePartRelease;
use App\Models\Part;
use App\Models\PartRelease;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Create extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Part::unofficial()
                ->where('vote_sort', 1)
                ->orderBy('part_type_id')
                ->orderBy('filename')
            )
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->selectable()
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->state( 
                            fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                        )
                        ->grow(false)
                        ->extraImgAttributes(['class' => 'object-scale-down w-[35px] max-h-[75px]']),
                    Stack::make([
                        TextColumn::make('filename')
                            ->weight(FontWeight::Bold)
                            ->sortable(),
                        TextColumn::make('description')
                            ->sortable(),
                    ])->alignment(Alignment::Start),
                    Stack::make([
                        ViewColumn::make('vote_sort')
                            ->view('tables.columns.part-status')
                            ->sortable()
                            ->grow(false)
                            ->label('Status'),
                        TextColumn::make('part_check_messages')
                            ->state(fn(Part $part) => implode(", ", $part->part_check_messages['errors']))
                            ->wrap()
                            ->alignment(Alignment::End),
                    ])->alignment(Alignment::End),
                ])->from('md')
            ])
            ->recordUrl(
                fn (Part $p): string => 
                    route($p->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $p])
            )
            ->paginated(false)
            ->recordClasses(fn (Part $p) => count($p->part_check_messages['errors']) > 0 ? '!bg-red-300' : null)
            ->bulkActions([
                BulkAction::make('create-release')
                    ->form([
                        Toggle::make('include-ldconfig'),
                        FileUpload::make('additional-files')
                    ])
                    ->action(fn (array $data, Collection $parts) => $this->createRelease($data, $parts))
                    ->successRedirectUrl(route('tracker.activity'))
            ]);
    }
    
    protected function createRelease(array $data, Collection $parts) : void 
    {
        $this->authorize('store', PartRelease::class);
        $addFiles = [];
        if (!is_null($data['additional-files'])) {
            foreach ($data['additional-files'] as $afile) {
                $addFiles[$afile->getClientOriginalName()] = $afile->get();
            }
        }
        MakePartRelease::dispatch($parts, Auth::user(), $data['include-ldconfig'] ?? false, $addFiles);
        $this->redirectRoute('tracker.activity');        
    }
    #[Layout('components.layout.tracker')]    
    public function render()
    {
        return view('livewire.release.create');
    }
}
