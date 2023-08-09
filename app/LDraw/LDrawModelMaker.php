<?php

namespace App\LDraw;

use App\Models\Part;

class LDrawModelMaker
{
    public function partMpd(Part $part, string $matrix = '1 0 0 0 1 0 0 0 1'): string
    {
        if ($part->isTexmap()) {
            return $part->get(true, true);
        }

        $topModelName = pathinfo($part->filename, PATHINFO_FILENAME) . '.ldr';
        $file = "0 FILE {$topModelName}\r\n1 16 0 0 0 {$matrix} {$part->name()}\r\n0 FILE {$part->name()}\r\n{$part->get()}\r\n";
        $sparts = $part->allSubparts();
        if ($part->isUnofficial()) {
            $sparts = $sparts->whereNull('unofficial_part_id');
        } else {
            $sparts = $sparts->whereNotNull('part_release_id');
        }
        foreach ($sparts ?? [] as $s) {
            if ($s->isTexmap()) {
                $file .= $s->get(true, true);
            } else {
                $file .= "0 FILE {$s->name()}\r\n{$s->get()}\n";
            }
        }
        return $file;
    }

    public function diff(Part $part1, Part $part2): string {
        $lines = collect(explode("\n", $part1->body->body))->filter(function (string $value) {
          return !empty($value) && $value[0] != "0";
        });
        $lines2 = collect(explode("\n", $part2->body->body))->filter(function (string $value) {
          return !empty($value) && $value[0] != "0";
        });
        $pattern = '#^([12345]) (\d+)#';
        $delcolor   = ['1' => '36', '2' => '12', '3' => '36', '4' => '36', '5' => '12'];
        $addcolor   = ['1' =>  '2', '2' => '10', '3' =>  '2', '4' =>  '2', '5' => '10'];
        $matchcolor = ['1' => '15', '2' =>  '8', '3' => '15', '4' => '15', '5' =>  '8'];
        $same = $lines->intersect($lines2)->transform(function (string $item) use ($pattern, $matchcolor) {
          return preg_replace($pattern, '$1 '. $matchcolor[$item[0]], $item);
        });
        $added = $lines2->diff($lines)->transform(function (string $item) use ($pattern, $addcolor) {
          return preg_replace($pattern, '$1 '. $addcolor[$item[0]], $item);
        });
        $removed = $lines->diff($lines2)->transform(function (string $item) use ($pattern, $delcolor) {
          return preg_replace($pattern, '$1 '. $delcolor[$item[0]], $item);
        });
        return implode("\n", array_merge($same->toArray(), $added->toArray(), $removed->toArray()));
    }
  
}