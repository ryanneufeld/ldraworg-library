<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Tables\Table;

class PartsFlaggedForDeletionTable extends BasicTable
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Part::where('delete_flag', true)->orderby('filename')
            )
            ->columns(PartTable::columns());
    }
}
