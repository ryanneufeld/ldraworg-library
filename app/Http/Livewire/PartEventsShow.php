<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class PartEventsShow extends Component
{
    use WithPagination;

    public $itemsPerPage = '20';
    public $order = 'latest';
    public $dt = '';
    public $unofficial = false;
    public $types = [];

    protected $queryString= [
        'itemsPerPage' => ['except' => '20', 'as' => 'n'],
        'order' => ['except' => 'latest'],
        'dt' => ['except' => ''],
        'types' => ['except' => []],
        'unofficial' => ['except' => false],
    ];

    public function updated($name, $value)
    {
        $this->resetPage();
    }

    public function render()
    {
        $filtersActive = $this->itemsPerPage != '20' || $this->order != 'latest' || !empty($this->dt) || !empty($this->types) || $this->unofficial;
        $pageItems = [20, 40, 80, 100];
        $orderItems = ['latest' => 'Newest First', 'oldest' => 'Oldest First'];

        $events = \App\Models\PartEvent::with(['part', 'user', 'part_event_type', 'release']);
        if (!empty($this->dt)) {
            $events->where('created_at', '>=', date_create($this->dt));
        }
        if (!empty($this->types)) {
            $events->where(function ($q) {
                foreach($this->types as $type) {
                    $q->orWhere('part_event_type_id', $type);
                }                    
            });
        }
        if ($this->unofficial) {
            $events->whereRelation('release', 'short', 'unof');
        }
        if ($this->order == 'oldest') {
            $events->oldest();
        }
        else {
            $events->latest();
        }
        return view('livewire.part-events-show', [
            'filtersActive' => $filtersActive, 
            'pageItems' => $pageItems,
            'orderItems' => $orderItems,
            'eventtypes' => \App\Models\PartEventType::all(), 'events' => $events->paginate($this->itemsPerPage)
        ]);
    }
}
