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
    public $exclude_reviews = false;

    protected $queryString= [
        'itemsPerPage' => ['except' => '500', 'as' => 'n'],
        'status' => ['except' => ''],
        'user_id' => ['except' => ''],
        'exclude_user' => ['except' => false],
        'exclude_reviews' => ['except' => false],
        'part_types' => ['except' => []],
    ];

    public function updated($name, $value)
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->unofficial == true) {
            $parts = \App\Models\Part::unofficial();
        }
        else {
            $parts = \App\Models\Part::official();
        }

        if ($this->unofficial && !empty($this->status)) {
            $parts->partStatus($this->status);
        }
        if (!empty($this->user_id) && is_numeric($this->user_id)) {
            if ($this->exclude_user) {
                $parts->where('user_id', '!=', $this->user_id);
            } else {
                $parts->where('user_id', $this->user_id);
            }
            
        }
        $types = array_filter($this->part_types, 'is_numeric');
        if (!empty($types)) {
            $parts->whereIn('part_type_id', $types);
        }
        if ($this->unofficial && !empty($this->exclude_reviews)) {
            $parts->whereDoesntHave('votes', function($q) {
                $q->where('user_id', auth()->user()->id);
            });
        }      
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.part.part-list', [
            'parts' => $parts->orderby('vote_sort')->orderBy('part_type_id')->orderBy('filename')->paginate($this->itemsPerPage)
        ]);
    }
}
