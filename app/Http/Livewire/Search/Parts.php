<?php

namespace App\Http\Livewire\Search;

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
    public $part_types = [];
    public $exclude_user = false;

    protected $queryString= [
        'search' => ['except' => '', 'as' => 's'],
        'scope' => ['except' => 'header'],
        'user_id' => ['except' => ''],
        'exclude_user' => ['except' => false],
        'status' => ['except' => ''],
        'part_types' => ['except' => []],
    ];

    public function updated() {
        $this->resetPage('unofficialPage');
        $this->resetPage('officialPage');
    }

    public function render()
    {
        $scopeOptions = [
            'filename' => 'Filename only',
            'description' => 'Filename and description',
            'header' => 'File header',
            'file' => 'Entire file'
        ];

        $scope = array_key_exists($this->scope, $scopeOptions) ? $this->scope : 'header';
        $uparts = Part::unofficial();
        $oparts = Part::official();
        if (!empty($this->user_id) && is_numeric($this->user_id)) {
            $opr = $this->exclude_user ? '!=' : '=';
            $uparts->where('user_id', $opr, $this->user_id);
            $oparts->where('user_id', $opr, $this->user_id);
        }
        if (!empty($this->status)) {
            $uparts->partStatus($this->status);
        }
        $types = array_filter($this->part_types, 'is_numeric');
        if (!empty($types)) {
            $uparts->whereIn('part_type_id', $types);
            $oparts->whereIn('part_type_id', $types);
        }
        $uparts->searchPart($this->search, $this->scope);
        $oparts->searchPart($this->search, $this->scope);
         
        $ucount = $uparts->count();
        $ocount = $oparts->count();
        $uparts = $uparts->orderBy('filename')->paginate('50', ['*'], 'unofficialPage');
        $oparts = $oparts->orderBy('filename')->paginate('50', ['*'], 'officialPage');
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.search.parts', compact('ucount','ocount','uparts', 'oparts','scope','scopeOptions'));
    }

}
