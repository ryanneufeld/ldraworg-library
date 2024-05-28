<?php

namespace App\LDraw;

use App\Models\Part;
use Illuminate\Database\Eloquent\Collection;

class PartRepository
{
    public function patternParts(Part $part): Collection
    {
        return $this->baseparts($part)
            ->filter(fn (Part $p) => preg_match('/^parts\/' . $p->basepart() . 'p(?:[a-z0-9]{2,3}|[0-9]{4})\.dat$/ui', $p->filename) === 1);
    }

    public function compositeParts(Part $part): Collection
    {
        return $this->baseparts($part)
            ->filter(fn (Part $p) => preg_match('/^parts\/' . $p->basepart() . 'c(?:[a-z0-9]{2}|[0-9]{4})(?:-f[0-9])?\.dat/ui', $p->filename) === 1);
    }

    protected function baseparts(Part $part): Collection
    {
        return Part::where('filename', 'LIKE', 'parts/' . $part->basepart() . '%.dat')->get();
    }
}