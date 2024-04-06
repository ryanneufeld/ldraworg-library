<?php

namespace App\Livewire\Tables;

use App\Models\Part;
use App\Tables\Part\PartTable;
use Filament\Tables\Table;

class PartDependenciesTable extends BasicTable
{
    public bool $official = false;
    public bool $parents = false;
    public Part $part;

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if ($this->parents !== false) {
                    $q = $this->official !== false ? $this->part->parents()->official() : $this->part->parents()->unofficial();
                } else {
                    $q = $this->official !== false ? $this->part->subparts()->official() : $this->part->subparts()->unofficial();
                }
                return $q;
            })
            ->heading(($this->official ? "Official" : "Unofficial") . ($this->parents ? " parent parts" : " subparts"))
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns(PartTable::columns())
            ->actions(PartTable::actions())
            ->recordUrl(
                fn (Part $p): string => 
                    route($p->isUnofficial() ? 'tracker.show' : 'official.show', ['part' => $p])
            )
            ->striped()
            ->paginated([50, 100, 250, 500])
            ->defaultPaginationPageOption(50)
            ->queryStringIdentifier(($this->official ? "official" : "unofficial") . ($this->parents ? "Parents" : "Subparts"));
    }
}
