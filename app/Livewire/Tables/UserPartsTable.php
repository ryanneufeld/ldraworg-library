<?php

namespace App\Livewire\Tables;

use App\Filament\Part\Tables\PartTable;
use App\Models\Part;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserPartsTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::unofficial()
                    ->where(fn (Builder $q) => $q->orWhere(fn (Builder $qu) => $qu->doesntHave('official_part')->where('user_id', Auth::user()->id))
                        ->orWhereHas('events', fn (Builder $qu) => $qu->unofficial()->where('user_id', Auth::user()->id)->whereRelation('part_event_type', 'slug', 'submit'))
                    )
            )
            ->defaultSort('created_at', 'desc')
            ->heading('MySubmits')
            ->columns(PartTable::columns())
            ->filters([
                SelectFilter::make('vote_sort')
                    ->options([
                        '1' => 'Certified',
                        '2' => 'Needs Admin Review',
                        '3' => 'Needs More Votes',
                        '5' => 'Errors Found',
                    ])
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->label('Unofficial Status'),
                SelectFilter::make('part_type_id')
                    ->relationship('type', 'name')
                    ->native(false)
                    ->multiple()
                    ->preload()
                    ->label('Part Type'),
                TernaryFilter::make('exclude_fixes')
                    ->label('Fix Status')
                    ->placeholder('All Parts')
                    ->trueLabel('Exclude official part fixes')
                    ->falseLabel('Only official part fixes')
                    ->queries(
                        true: fn (Builder $q) => $q->doesntHave('official_part'),
                        false: fn (Builder $q) => $q->has('official_part'),
                        blank: fn (Builder $q) => $q,
                    ),
            ])
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->queryStringIdentifier('userParts');
    }
}
