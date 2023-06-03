<?php

namespace App\View\Components\Part;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Part;

class Status extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Part $part,
        public bool $showStatus = false
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        switch($this->part->vote_sort) {
            case 1:
                $color = 'brtgrn';
                $text = 'Certified!';
                break;
            case 2:
                $color = 'blue';
                $text = 'Needs Admin Review';
                break;
            case 3:
                $color = 'gray';
                $text = $this->part->vote_summary['A'] +  $this->part->vote_summary['C'] == 1 ? 'Needs 1 More Vote' : 'Needs 2 More Votes';
                break;
            case 5:
                $color = 'red';
                $text = 'Errors Found';
                break;
                        
        }
        $text = $this->showStatus ? $text : '';
        $code = "(";
        foreach(['T', 'A', 'C', 'H'] as $letter) {
            $code .= str_repeat($letter, $this->part->vote_summary[$letter]);
        } 
        $code .=  $this->part->vote_summary['F'] . ")";

        return view('components.part.status', compact('text','code','color'));
    }
}
