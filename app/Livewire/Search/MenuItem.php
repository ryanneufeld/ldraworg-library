<?php

namespace App\Livewire\Search;

use App\Models\Part;
use App\Settings\LibrarySettings;
use Livewire\Component;

class MenuItem extends Component
{
    public string $search;
    public array $results = [];
    public bool $hasResults = false;

    public function doSearch()
    {
        $this->results = [];
        $this->hasResults = false;
        if (empty($this->search) || !is_string($this->search)) {
            return;
        }
        $limit = app(LibrarySettings::class)->quick_search_limit;
        $uparts = Part::unofficial()->searchPart($this->search, 'header')->orderBy('filename')->take($limit)->get();
        $oparts = Part::official()->searchPart($this->search, 'header')->orderBy('filename')->take($limit)->get();
        if ($uparts->count() > 0) {
            $this->hasResults = true;
            foreach($uparts as $part) {
                $this->results['Unofficial Parts'][$part->id] = ['name' => $part->name(), 'description' => $part->description];
            }
        }
        if ($oparts->count() > 0) {
            $this->hasResults = true;
            foreach($oparts as $part) {
                $this->results['Official Parts'][$part->id] = ['name' => $part->name(), 'description' => $part->description];
            }
        }
    }

    public function render()
    {
        return view('livewire.search.menu-item');
    }
}
