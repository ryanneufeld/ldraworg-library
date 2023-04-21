<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class PartsShow extends Component
{
    use WithPagination;

    public $unofficial = false;
    public $itemsPerPage = '500';
    public $subset = '';
    public $user_id = '';
    public $part_types = [];

    protected $queryString= [
        'itemsPerPage' => ['except' => '500', 'as' => 'n'],
        'subset' => ['except' => ''],
        'user_id' => ['except' => ''],
        'part_types' => ['except' => []],
    ];

    public function updated($name, $value)
    {
        $this->resetPage();
    }

    public function render()
    {
        $pageItems = [50 => '50', 100 => '100', 250 => '250', 500 => '500'];
        $userItems = \App\Models\User::whereHas('parts', function ($query) {
            if ($this->unofficial) {
              $query->unofficial();
            }
            else {
              $query->official();
            }
        })->orderBy('name')->pluck('name', 'id')->all();
        $subsetItems = [
            'certified' => 'Certified', 
            'adminreview' => 'Needs Admin Review', 
            'memberreview' => 'Needs More Votes', 
            'needsubfile' => 'Uncertified Subfiles', 
            'held' => 'Hold', 
            '2certvotes' => '2 (or more) Certify Votes', 
            '1certvote' => '1 Certify Vote'
        ];
        $parttypeItems = \App\Models\PartType::pluck('name', 'id')->all();

        if ($this->unofficial == true) {
            $parts = \App\Models\Part::unofficial();
        }
        else {
            $parts = \App\Models\Part::official();
        }

        if ($this->unofficial && !empty($this->subset) && array_key_exists($this->subset, $subsetItems)) {
            switch ($this->subset) {
                case 'certified':
                    $parts->where('vote_sort', 1);
                    break; 
                case 'adminreview':
                    $parts->where('vote_sort', 2);
                    break; 
                case 'memberreview':
                    $parts->where('vote_sort', 3);
                    break; 
                case 'needsubfile':
                    $parts->where('vote_sort', 4);
                    break; 
                case 'held':
                    $parts->where('vote_sort', 5);
                    break; 
                case '2certvotes':
                    $parts->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
                        $q->where('vote_type_code', 'C');
                    }, '>=', 2);
                    break; 
                case '1certvote':
                    $parts->where('vote_sort', '<>', 5)->where('vote_sort', '<>', 1)->whereHas('votes', function ($q) {
                        $q->where('vote_type_code', 'C');
                    }, '=', 1);
                    break; 
        
            }
        }
        if (!empty($this->user_id) && array_key_exists($this->user_id, $userItems)) {
            $parts->where('user_id', $this->user_id);
        }
        if (!empty($this->part_types)) {
            $parts->where(function ($q) use ($parttypeItems) {
                foreach($this->part_types as $type)  {
                    if (array_key_exists($type, $parttypeItems)) {
                        $q->orWhere('part_type_id', $type);
                    }
                }                    
            });
        }
        $filtersActive = 
            $this->itemsPerPage != '500' || 
            array_key_exists($this->user_id, $userItems) || 
            !empty($this->part_types) || 
            array_key_exists($this->subset, $subsetItems);

        return view('livewire.parts-show', [
            'filtersActive' => $filtersActive,
            'pageItems' => $pageItems,
            'subsetItems' => $subsetItems,
            'userItems' => $userItems,
            'parttypeItems' => $parttypeItems,
            'parts' => $parts->orderby('vote_sort')->orderBy('part_type_id')->orderBy('filename')->paginate($this->itemsPerPage)
        ]);
    }
}
