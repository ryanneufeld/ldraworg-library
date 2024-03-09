<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Tables\Table;

class PartReadyForAdminTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::adminReady()
                    ->orderby('vote_sort')
                    ->orderBy('part_type_id')
                    ->oldest()
            )
            ->columns(PartTable::columns());
    }

}
