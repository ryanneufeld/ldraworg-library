<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Tables\Table;

class NextReleasePartsTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::unofficial()
                    ->where('vote_sort', 1)
                    ->where('can_release', true)
                    ->orderBy('part_type_id')
                    ->orderBy('filename')
            )
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns(PartTable::columns())
            ->actions(PartTable::actions())
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->striped()
            ->paginated([50, 100, 250, 500])
            ->defaultPaginationPageOption(50);
    }

}
