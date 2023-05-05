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
    public $part_types = [];

    protected $queryString= [
        'search' => ['except' => '', 'as' => 's'],
        'scope' => ['except' => 'header'],
        'user_id' => ['except' => ''],
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
        if (!empty($this->user_id)) {
            $uparts->where('user_id', $this->user_id);
            $oparts->where('user_id', $this->user_id);
        }
        if (!empty($this->part_types)) {
            $uparts->whereIn('part_type_id', $this->part_types);
            $oparts->whereIn('part_type_id', $this->part_types);
        }
        $uparts->searchPart($this->search, $this->scope);
        $oparts->searchPart($this->search, $this->scope);
         
        $ucount = $uparts->count();
        $ocount = $oparts->count();
        $uparts = $uparts->orderBy('filename')->paginate('50', ['*'], 'unofficialPage');
        $oparts = $oparts->orderBy('filename')->paginate('50', ['*'], 'officialPage');
        return view('livewire.search.parts', compact('ucount','ocount','uparts', 'oparts','scope','scopeOptions'));
    }

}
