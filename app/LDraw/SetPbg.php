<?php

namespace App\LDraw;

use App\LDraw\Rebrickable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SetPbg
{
    static function pbg(string $set_number): string
    {
        $rb = app(Rebrickable::class);
        $set = $rb->getSet($set_number);
        $rb_parts = collect($rb->getSetParts($set_number));
        $result = array("[options]",
                  "kind=basic",
                  "caption=Set {$set['set_num']} - {$set['name']}",
                  "description=Parts in set {$set['set_num']}",
                  "sortDesc=false",
                  "sortOn=description",
                  "sortCaseInSens=true",
                  "<items>");
        $parts = []; 
        foreach($rb_parts->whereNotNull('part.external_ids.LDraw.0') as $rb_part) {
            $rb_part = Arr::dot($rb_part);
            $rb_part_num = $rb_part['part.part_num'] ?? null;
            $ldraw_part = $rb_part['part.external_ids.LDraw.0'] ?? null;
            $color = $rb_part['color.external_ids.LDraw.ext_ids.0'] ?? null;
            $quantity = $rb_part['quantity'] ?? null;
            if (array_key_exists($rb_part_num, $parts)) {
                $parts[$rb_part_num]['colors'][$color] = $quantity;
            } else {
                $parts[$rb_part_num] = ['ldraw_part' => $ldraw_part, 'colors' => [$color => $quantity]];
            }
        }
        $prints = $rb_parts->whereNull('part.external_ids.LDraw.0')->whereNotNull('part.print_of')->pluck('part.print_of')->all();
        if (count($prints) > 0) {
            $print_parts = collect($rb->getParts($prints));
            foreach($rb_parts->whereNull('part.external_ids.LDraw.0')->whereNotNull('part.print_of') as $rb_part) {
                $rb_part = Arr::dot($rb_part);
                $print = $rb_part['part.print_of'];
                if ($print_parts->where('part_num', $print)->whereNotNull('external_ids.LDraw.0')->count() == 0) {
                    continue;
                }
                $print = $print_parts->where('part_num', $print)->first();
                $ldraw_part = $print['external_ids']['LDraw'][0];
                $rb_part_num = $print['part_num'] ?? null;
                $color = $rb_part['color.external_ids.LDraw.ext_ids.0'] ?? null;
                $quantity = $rb_part['quantity'] ?? null;
                if (array_key_exists($rb_part_num, $parts) && array_key_exists($color, $parts[$rb_part_num]['colors'])) {
                    $parts[$rb_part_num]['colors'][$color] = $parts[$rb_part_num]['colors'][$color] + $quantity;
                } elseif (array_key_exists($rb_part_num, $parts)) {
                    $parts[$rb_part_num]['colors'][$color] = $quantity;
                } else {
                    $parts[$rb_part_num] = ['ldraw_part' => $ldraw_part, 'colors' => [$color => $quantity]];
                }
            }    
        }
        foreach($parts as $part) {
            foreach ($part['colors'] as $color => $quantity) {
                if (is_array($quantity)) {
                    dd($part);
                }
                $filename = $part['ldraw_part'];
                $result[] = "{$filename}.dat: [color={$color}][count={$quantity}]";
            }
        }
        
        return implode("\n", $result);
    }
}