<?php

namespace App\Livewire\Tables;

use App\Models\PartEvent;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserPartEventsTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PartEvent::unofficial()
                    ->whereHas('part', fn (Builder $q) => $q->doesntHave('official_part')->where('user_id', Auth::user()->id)
                    )
            )
            ->defaultSort('created_at', 'desc')
            ->heading('Events On My Submits')
            ->columns([
                Split::make([
                    ViewColumn::make('part_event_type')
                        ->view('components.event.icon.filament-table-icon')
                        ->grow(false),
                    TextColumn::make('created_at')
                        ->since()
                        ->sortable()
                        ->label('Date/Time')
                        ->grow(false),
                    ImageColumn::make('image')
                        ->state(
                            function (PartEvent $event) {
                                if (! is_null($event->part)) {
                                    return version("images/library/{$event->part->libFolder()}/".substr($event->part->filename, 0, -4).'_thumb.png');
                                } else {
                                    return asset('images/library/placeholder.png');
                                }
                            }
                        )
                        ->extraImgAttributes(['class' => 'object-scale-down w-[35px] max-h-[75px]'])
                        ->grow(false),
                    TextColumn::make('user.name')
                        ->description(fn (PartEvent $e): string => $e->user->realname ?? '')
                        ->grow(false)
                        ->visibleFrom('md'),
                    TextColumn::make('part.filename')
                        ->state(
                            fn (PartEvent $e) => ! is_null($e->part) ? $e->part->filename : $e->deleted_filename
                        )
                        ->description(fn (PartEvent $e): string => ! is_null($e->part) ? $e->part->description : $e->deleted_description)
                        ->label('Part')
                        ->visibleFrom('md'),
                    Stack::make([
                        TextColumn::make('user.name')
                            ->description(fn (PartEvent $e): string => $e->user->realname ?? '')
                            ->grow(false),
                        TextColumn::make('part.filename')
                            ->state(
                                fn (PartEvent $e) => ! is_null($e->part) ? $e->part->filename : $e->deleted_filename
                            )
                            ->description(fn (PartEvent $e): string => ! is_null($e->part) ? $e->part->description : $e->deleted_description)
                            ->label('Part'),
                    ])->hiddenFrom('sm'),
                    ViewColumn::make('status')
                        ->view('tables.columns.event-part-status')
                        ->label('Status')
                        ->grow(false),
                ]),
            ])
            ->recordUrl(
                fn (PartEvent $e): string => ! is_null($e->part) ? route($e->part->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $e->part]) : ''
            )
            ->queryStringIdentifier('userPartEvents');
    }
}
