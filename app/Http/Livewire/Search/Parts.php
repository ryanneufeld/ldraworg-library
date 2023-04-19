<?php

namespace App\Http\Livewire\Search;

use Livewire\Component;
use App\Models\Part;

class Parts extends Component
{
    public $search = '';
    public $scope = 'header';
    public $uparts;
    public $oparts;

    protected $queryString= [
        'search' => ['except' => '', 'as' => 's'],
        'scope' => ['except' => 'header']
    ];

    public function mount()
    {
        $this->search();
    }

    public function render()
    {
        return view('livewire.search.parts');
    }

    public function search() {
        $this->scope = in_array($this->scope, ['filename', 'description', 'header', 'file'], true) ? $this->scope : 'header';
            $parts = Part::searchPart($this->search, $this->scope)->orderBy('filename')->get();
            $this->uparts = $parts->where('release.short', 'unof');
            $this->oparts = $parts->where('release.short', '<>', 'unof');    
    }
}
