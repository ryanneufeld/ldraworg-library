<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class PartEventsShow extends Component
{
    use WithPagination;

    public $itemsPerPage = '20';
    public $order = 'latest';
    public $dt;
    public $unofficial = false;
    public $types = '';

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
        $event_types_ids = array_filter(explode(',', $this->types), 'is_numeric');
        
        if (count($event_types_ids) > 0) {
            $this->types = implode(',', $event_types_ids);
        } else {
            $this->types = '';
        }
        
        $filtersActive = $this->itemsPerPage != '20' || $this->order != 'latest' || !empty($this->dt) || !empty($this->types) || $this->unofficial;
        $orderItems = ['latest' => 'Newest First', 'oldest' => 'Oldest First'];
        $dt = empty($this->dt) ? null : date_create($this->dt);
        //$types = array_filter($this->types, 'is_numeric');
        $events = \App\Models\PartEvent::with(['part', 'user', 'part_event_type', 'release'])->
            when(!empty($dt), function ($q) use ($dt) {
                $q->where('created_at', '>=', $dt);
            })->
            when(count($event_types_ids) > 0, function ($q) use ($event_types_ids) {
                $q->whereIn('part_event_type_id', $event_types_ids);
            })->
            when($this->unofficial, function ($q) {
                $q->unofficial();
            })->
            when($this->order == 'oldest', function ($q) {
                $q->oldest();
            }, function($q) {
                $q->latest();
            })->paginate($this->itemsPerPage);
            return view('livewire.part-events-show', compact('filtersActive', 'orderItems', 'events'));
    }
}
