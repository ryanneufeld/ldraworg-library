<?php
namespace App\Livewire;
 
use App\Models\PartEvent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
 
class PartEventsShow extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(PartEvent::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                ViewColumn::make('part_event_type')
                    ->view('components.event.icon.filament-table-icon')
                    ->alignCenter()
                    ->label('Event'),
                TextColumn::make('user.name')
                    ->description(fn (PartEvent $e): string => $e->user->realname ?? ''),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->label('Date/Time'),
                ImageColumn::make('image')
                    ->state(
                        function (PartEvent $event) {
                            if (!is_null($event->part)) {
                                return version("images/library/{$event->part->libFolder()}" . substr($event->part->filename, 0, -4) . '_thumb.png');
                            } else {
                                return '';
                            }
                        }
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('part.filename')
                    ->state(
                        fn (PartEvent $e) =>
                            !is_null($e->part) ? $e->part->filename : $e->deleted_filename
                    )
                    ->description(fn (PartEvent $e): string => !is_null($e->part) ? $e->part->description : $e->deleted_description)
                    ->label('Part'),
                ViewColumn::make('status')
                    ->view('tables.columns.event-part-status')
                    ->label('Status'),
            ])
            ->filters([
                SelectFilter::make('part_event_type_id')
                    ->relationship('part_event_type', 'name')
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->label('Event Type'),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->label('Author'),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->label('Start Date/Time'),
                Filter::make('part_release_id')
                    ->query(fn (Builder $query): Builder => $query->whereNull('part_release_id'))
                    ->toggle()
                    ->label('Only unofficial part events'),
            ], layout: FiltersLayout::AboveContent)
                ->persistFiltersInSession()
            ->recordUrl(
                fn (PartEvent $e): string => 
                    !is_null($e->part) ? route($e->part->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $e->part]) : ''
            )
            ->striped()
            ->recordClasses(fn (PartEvent $e) => !is_null($e->part) && !$e->part->isUnofficial() ? 'bg-green-300' : '' );
    }
    
    public function render(): View
    {
        return view('livewire.part-events-show');
    }
}