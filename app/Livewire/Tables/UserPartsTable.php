<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
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
                    ->where(fn (Builder $q) =>
                        $q->orWhere(fn (Builder $qu) => $qu->doesntHave('official_part')->where('user_id', Auth::user()->id))
                            ->orWhereHas('events', fn (Builder $qu) => $qu->unofficial()->where('user_id', Auth::user()->id)->whereRelation('part_event_type', 'slug', 'submit'))
                    )
            )
            ->defaultSort('created_at', 'desc')
            ->heading('MySubmits')
            ->columns(PartTable::columns())
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->queryStringIdentifier('userParts');
    }

}
