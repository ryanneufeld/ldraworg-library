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

    protected $queryString= [
        'search' => ['except' => '', 'as' => 's'],
        'scope' => ['except' => 'header']
    ];

    public function updated() {
        $this->resetPage('unofficialPage');
        $this->resetPage('officialPage');
    }

    public function render()
    {
        $scope = in_array($this->scope, ['filename', 'description', 'header', 'file'], true) ? $this->scope : 'header';
        $ucount = Part::unofficial()->searchPart($this->search, $this->scope)->count();
        $ocount = Part::official()->searchPart($this->search, $this->scope)->count();
        $uparts = Part::unofficial()->searchPart($this->search, $this->scope)->orderBy('filename')->paginate('50', ['*'], 'unofficialPage');
        $oparts = Part::official()->searchPart($this->search, $this->scope)->orderBy('filename')->paginate('50', ['*'], 'officialPage');    
        return view('livewire.search.parts', compact('ucount','ocount','uparts', 'oparts','scope'));
    }

}
