<?php

namespace App\LDraw;

use App\LDraw\Rebrickable;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;

class SetPbg
{
    public MessageBag $messages;
    protected array $parts = [];
    protected ?array $set = null;
    
    public function __construct()
    {
        $this->messages = new MessageBag();
    }
    
    public function pbg(?string $set_number = null): string|false
    {
        $this->messages = new MessageBag();

        if ((is_null($set_number) && !is_null($this->set)) ||
            (!is_null($this->set) && $this->set['set_num'] == $set_number)
            && count($this->parts) > 0)
        {
            return $this->makePbg();
        } elseif (is_null($set_number) && is_null($this->set)) {
            $this->messages->add('errors', 'Set number empty');
            return false;
        }
        
        $this->parts = []; 

        $rb = app(Rebrickable::class);
        $this->set = $rb->getSet($set_number);

        if (is_null($this->set)) {
            $this->messages->add('errors', 'Set Not Found');
            return false;
        }

        $rb_parts = collect($rb->getSetParts($set_number));
        foreach($rb_parts->whereNotNull('part.external_ids.LDraw.0') as $rb_part) {
            $this->addPart($rb_part);
        }
        
        $prints = $rb_parts->whereNull('part.external_ids.LDraw.0')->whereNotNull('part.print_of')->pluck('part.print_of')->all();
        if (count($prints) > 0) {
            $print_parts = collect($rb->getParts($prints));
            foreach($rb_parts->whereNull('part.external_ids.LDraw.0')->whereNotNull('part.print_of') as $rb_part) {
                $rb_part = Arr::dot($rb_part);
                if ($print_parts->where('part_num', $rb_part['part.print_of'])->whereNotNull('external_ids.LDraw.0')->count() == 0) {
                    continue;
                }
                $print = $print_parts->where('part_num', $rb_part['part.print_of'])->first();
                $this->addPart($rb_part, $print);
            }    
        }
        return $this->makePbg();
    }
    
    protected function addPart(array $rb_part, ?array $unprinted_part = null): void
    {
        $rb_part = Arr::dot($rb_part);
        if (!array_key_exists('color.external_ids.LDraw.ext_ids.0', $rb_part)) {
            $this->messages->add('errors', 'LDraw color not found for ' . $rb_part['color.name']);
            return;
        }
        if (!is_null($unprinted_part)) {
            $unprinted_part = Arr::dot($unprinted_part);
            $ldraw_part = $unprinted_part['external_ids.LDraw.0'];
            $rb_part_num = $unprinted_part['part_num'];
        } else {
            $rb_part_num = $rb_part['part.part_num'];
            $ldraw_part = $rb_part['part.external_ids.LDraw.0'];
        }
        $color = $rb_part['color.external_ids.LDraw.ext_ids.0'];
        $quantity = $rb_part['quantity'];
        
        if (array_key_exists($rb_part_num, $this->parts) && array_key_exists($color, $this->parts[$rb_part_num]['colors'])) {
            $this->parts[$rb_part_num]['colors'][$color] += $quantity;
        } elseif (array_key_exists($rb_part_num, $this->parts)) {
            $this->parts[$rb_part_num]['colors'][$color] = $quantity;
        } else {
            $this->parts[$rb_part_num] = ['ldraw_part' => $ldraw_part, 'colors' => [$color => $quantity]];
            if (!is_null($unprinted_part)) {
                $this->messages->add('unpatterned', $rb_part['part.part_num'] . " ({$ldraw_part})");
            }
        }
    }
    
    protected function makePbg(): string
    {
        $num = $this->set['set_num'];
        $name = $this->set['name'];
        $result = [
            "[options]",
            "kind=basic",
            "caption=Set {$num} - {$name}",
            "description=Parts in set {$num}",
            "sortDesc=false",
            "sortOn=description",
            "sortCaseInSens=true",
            "<items>"
        ];
        foreach($this->parts as $part) {
            foreach ($part['colors'] as $color => $quantity) {
                $filename = $part['ldraw_part'];
                $result[] = "{$filename}.dat: [color={$color}][count={$quantity}]";
            }
        }
        
        return implode("\n", $result);
    }
    
}