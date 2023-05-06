<?php

namespace App\Http\Livewire\Part;

use Livewire\Component;
use Livewire\WithPagination;

class PartList extends Component
{
    use WithPagination;

    public $unofficial = false;
    public $itemsPerPage = '500';
    public $status = '';
    public $user_id = '';
    public $part_types = [];
    public $exclude_user = false;

    protected $queryString= [
        'itemsPerPage' => ['except' => '500', 'as' => 'n'],
        'status' => ['except' => ''],
        'user_id' => ['except' => ''],
        'exclude_user' => ['except' => false],
        'part_types' => ['except' => []],
    ];

    public function updated($name, $value)
    {
        $this->resetPage();
    }

    public function render()
    {
        $statusOptions = [
            'certified' => 'Certified', 
            'adminreview' => 'Needs Admin Review', 
            'memberreview' => 'Needs More Votes', 
            'needsubfile' => 'Uncertified Subfiles', 
            'held' => 'Hold', 
            '2certvotes' => '2 (or more) Certify Votes', 
            '1certvote' => '1 Certify Vote'
        ];
        if ($this->unofficial == true) {
            $parts = \App\Models\Part::unofficial();
        }
        else {
            $parts = \App\Models\Part::official();
        }

        if ($this->unofficial && !empty($this->status)) {
            switch ($this->status) {
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
        if (!empty($this->user_id)) {
            if ($this->exclude_user) {
                $parts->where('user_id', '!=', $this->user_id);
            } else {
                $parts->where('user_id', $this->user_id);
            }
            
        }
        if (!empty($this->part_types)) {
            $parts->whereIn('part_type_id', $this->part_types);
        }

        return view('livewire.part.part-list', [
            'statusOptions' => $statusOptions,
            'parts' => $parts->orderby('vote_sort')->orderBy('part_type_id')->orderBy('filename')->paginate($this->itemsPerPage)
        ]);
    }
}
