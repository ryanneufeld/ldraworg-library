<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class PartEventsShow extends Component
{
    use WithPagination;

    public $itemsPerPage = '20';
    public $order = 'latest';
    public $dt;
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
        $orderItems = ['latest' => 'Newest First', 'oldest' => 'Oldest First'];
        $dt = empty($this->dt) ? null : date_create($this->dt);
        $types = array_filter($this->types, 'is_numeric');
        $events = \App\Models\PartEvent::with(['part', 'user', 'part_event_type', 'release'])->
            when(!empty($dt), function ($q) use ($dt) {
                $q->where('created_at', '>=', $dt);
            })->
            when(!empty($types), function ($q) use ($types) {
                $q->whereIn('part_event_type_id', array_values($types));
            })->
            when($this->unofficial, function ($q) {
                $q->unofficial();
            })->
            when($this->order == 'oldest', function ($q) {
                $q->oldest();
            }, function($q) {
                $q->latest();
            })->paginate($this->itemsPerPage);
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.part-events-show', compact('filtersActive', 'orderItems', 'events'));
    }
}
