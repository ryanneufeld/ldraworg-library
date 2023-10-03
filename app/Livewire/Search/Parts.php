<?php

namespace App\Livewire\Search;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Part;

class Parts extends Component
{
    use WithPagination;

    public $search = '';
    public $scope = 'header';
    public $user_id = '';
    public $status = '';
    public $part_types = '';
    public $exclude_user = false;
    public $include_history = false;
    
    protected $queryString= [
        'search' => ['except' => '', 'as' => 's'],
        'scope' => ['except' => 'header'],
        'user_id' => ['except' => ''],
        'exclude_user' => ['except' => false],
        'include_history' => ['except' => false],
        'status' => ['except' => ''],
        'part_types' => ['except' => []],
    ];

    public function updated() {
        $this->resetPage('unofficialPage');
        $this->resetPage('officialPage');
    }

    public function searchpart() {
        return;
    }

    public function render()
    {
        $part_types_ids = array_filter(explode(',', $this->part_types), 'is_numeric');
        
        if (count($part_types_ids) > 0) {
            $this->part_types = implode(',', $part_types_ids);
        } else {
            $this->part_types = '';
        }

        $scopeOptions = [
            'filename' => 'Filename only',
            'description' => 'Filename and description',
            'header' => 'File header',
            'file' => 'Entire file (very slow)'
        ];

        $scope = array_key_exists($this->scope, $scopeOptions) ? $this->scope : 'header';
        $uparts = Part::unofficial();
        $oparts = Part::official();
        if (!empty($this->user_id) && is_numeric($this->user_id)) {
            $opr = $this->exclude_user ? '!=' : '=';
            if ($this->include_history) {
                $uparts->where(function ($q) use ($opr) {
                    $q->orWhere('user_id', $opr, $this->user_id)->orWhereHas('history', function($qu) use ($opr) {
                        $qu->where('user_id', $opr, $this->user_id);
                    });
                });
                $oparts->where(function ($q) use ($opr) {
                    $q->orWhere('user_id', $opr, $this->user_id)->orWhereHas('history', function($qu) use ($opr) {
                        $qu->where('user_id', $opr, $this->user_id);
                    });
                });
            } else {
                $uparts->where('user_id', $opr, $this->user_id);
                $oparts->where('user_id', $opr, $this->user_id);
            }
        }
        if (!empty($this->status)) {
            $uparts->partStatus($this->status);
        }
        if (count($part_types_ids) > 0) {
            $uparts->whereIn('part_type_id', $part_types_ids);
            $oparts->whereIn('part_type_id', $part_types_ids);
        }
        
        if (!empty(trim($this->search))) {
            $uparts->searchPart($this->search, $this->scope);
            $oparts->searchPart($this->search, $this->scope);
        }
        
        $ucount = $uparts->count();
        $ocount = $oparts->count();
        $uparts = $uparts->orderBy('filename')->paginate('50', ['*'], 'unofficialPage');
        $oparts = $oparts->orderBy('filename')->paginate('50', ['*'], 'officialPage');
        $this->dispatch('jquery');
        return view('livewire.search.parts', compact('ucount', 'ocount', 'uparts', 'oparts', 'scope', 'scopeOptions'));
    }

}
