<?php

namespace App\LDraw;

use App\Models\Part;

class LDrawModelMaker
{
    public function __construct(
        public readonly PartManager $manager
    ) {}

    public function makePartMpd(Part $part, string $matrix = '1 0 0 0 1 0 0 0 1'): string
    {
        $topModelName = pathinfo($part->filename, PATHINFO_FILENAME) . '.ldr';
        $file = "0 FILE $topModelName.ldr\r\n1 16 0 0 0 {$matrix} {$part->name()}\r\n0 FILE {$part->name()}\r\n{$part->get()}\r\n";
        $sparts = $this->manager->allSubparts($part);
        if ($part->isUnofficial()) {
            $sparts = $sparts->whereNull('unofficial_part_id');
        } else {
            $sparts = $sparts->whereNotNull('part_release_id');
        }
        foreach ($sparts ?? [] as $s) {
            $file .= "0 FILE {$s->name()}\r\n{$s->get()}\r\n";
        }
        return $file;
    }
}