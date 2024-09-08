<?php

namespace App\Livewire\Tables;

use App\Filament\Part\Tables\PartTable;
use App\Models\Part;
use Filament\Tables\Table;

class PartsFlaggedForDeletionTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::where('delete_flag', true)->orderby('filename')
            )
            ->heading('Parts Flagged For Deletion')
            ->columns(PartTable::columns())
            ->recordUrl(fn (Part $p): string => route('tracker.show', ['part' => $p]))
            ->queryStringIdentifier('deleteFlagged');
    }
}
