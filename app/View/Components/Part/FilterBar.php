<?php

namespace App\View\Components\Part;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FilterBar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $items = "page,user,status,parttype",
        public $pageitems = "50,100,250,500",
        public $unofficial = false
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $items = explode(',',$this->items);
        $pageItems = array_combine(explode(',', $this->pageitems), explode(',', $this->pageitems)); //[50 => '50', 100 => '100', 250 => '250', 500 => '500'];
        $userItems = \App\Models\User::whereHas('parts', function ($query) {
            if (strpos($this->items, 'user:unofficial') !== false) {
              $query->unofficial();
            } elseif (strpos($this->items, 'user:official') !== false) {
              $query->official();
            }
        })->orderBy('name')->pluck('name', 'id')->all();
        $statusItems = [
            'certified' => 'Certified', 
            'adminreview' => 'Needs Admin Review', 
            'memberreview' => 'Needs More Votes', 
            'needsubfile' => 'Uncertified Subfiles', 
            'held' => 'Hold', 
            '2certvotes' => '2 (or more) Certify Votes', 
            '1certvote' => '1 Certify Vote'
        ];
        $parttypeItems = \App\Models\PartType::pluck('name', 'id')->all();
        $aware = [];
        if (in_array('page', $items)) {
            $aware[] = 'itemsPerPage';
        }
        if (in_array('user', $items)) {
            $aware[] = 'user_id';
        }
        if (in_array('status', $items)) {
            $aware[] = 'status';
        }
        if (in_array('parttype', $items)) {
            $aware[] = 'part_types';
        }
        return view('components.part.filter-bar', compact('pageItems', 'userItems', 'statusItems', 'parttypeItems', 'aware'));
    }
}
