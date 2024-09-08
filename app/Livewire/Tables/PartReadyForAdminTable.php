<?php

namespace App\Livewire\Tables;

use App\Filament\Part\Tables\PartTable;
use App\Models\Part;
use Filament\Tables\Table;

class PartReadyForAdminTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::with(['votes', 'official_part', 'descendantsAndSelf'])->adminReady()
            )
            ->defaultSort('created_at', 'asc')
            ->heading('Parts Ready For Admin')
            ->columns(PartTable::columns())
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->queryStringIdentifier('readyForAdmin');
    }
}
