<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Filters\AuthorFilter;
use App\Tables\Part\PartTable;
use Filament\Forms\Get;
use Filament\Tables\Filters\SelectFilter;
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
                // Parts folder only
                ->whereHas('type', fn (Builder $q) => $q->where('folder', 'parts/'))
                // No holds
                ->whereDoesntHave('descendantsAndSelf', function ($q) {
                    $q->where('vote_sort', '5');
                })
                // At least one part in the chain with
                ->whereHas('descendantsAndSelf', function (Builder $q) {
                    // Only Needs More Votes
                    $q->where('vote_sort', 3)
                    // No votes from user
                    ->whereDoesntHave('votes', fn (Builder $qu) => $qu->where('user_id', Auth::user()->id))
                    // No submit events from user
                    ->whereDoesntHave('events', fn (Builder $qu) =>
                        $qu->where('user_id', Auth::user()->id)->whereRelation('part_event_type', 'slug', 'submit')->unofficial()
                    )
                    // Not authored by user unless a fix
                    ->where(function (Builder $q) {
                        $q->orWhereHas('official_part')
                        ->orWhere(fn (Builder $qu) =>
                            $qu->whereDoesntHave('official_part')->where('user_id', '<>', Auth::user()->id)
                        );
                    });
                }, '>=', 1)
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
