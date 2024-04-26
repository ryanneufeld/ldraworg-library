<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PartReadyForUserTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::unofficial()
                ->whereHas('type', fn (Builder $q) => $q->where('folder', 'parts/'))
                ->whereDoesntHave('descendantsAndSelf', function ($q) {
                    $q->where('vote_sort', '5');
                })
                ->where(fn (Builder $q) =>
                    $q->orWhere(fn (Builder $q) =>
                        $q->whereHas('official_part')
                        ->whereDoesntHave('events', fn (Builder $q) =>
                            $q->where('user_id', Auth::user()->id)->whereRelation('part_event_type', 'slug', 'submit')->unofficial())
                    )
                    ->orWhere(fn (Builder $q) =>
                        $q->whereDoesntHave('official_part')
                        ->whereDoesntHave('events', fn (Builder $q) => 
                            $q->where('user_id', Auth::user()->id)->whereRelation('part_event_type', 'slug', 'submit')->unofficial())
                        ->where('user_id', '<>', Auth::user()->id)
                    )
                )
                ->whereHas('descendantsAndSelf', fn (Builder $q) =>
                    $q->whereDoesntHave('votes', fn (Builder $q) => $q->where('user_id', Auth::user()))
                , '>=', 1)
            )
            ->defaultSort('created_at', 'asc')
            ->heading('Parts Ready For Your Vote')
            ->description('This table show parts where the part and/or the parts in the subfile chain can recieve a vote from you')
            ->columns(PartTable::columns())
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->queryStringIdentifier('readyForUser')
            ->persistFiltersInSession();;
    }

}
