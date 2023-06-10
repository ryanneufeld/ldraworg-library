<?php

namespace App\View\Components\Part;

use App\Models\Part;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PartCheckMessage extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Part $part,
        protected \App\LDraw\PartChecker $checker
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $errors = $this->checker->check($this->part);
        $show = (!is_null($errors) || 
        (!$this->part->hasCertifiedParent() && $this->part->vote_sort == 1 && $this->part->type->folder != "parts/" && !is_null($this->part->official_part_id)) || 
        $this->part->hasUncertifiedSubparts() ||
        $this->part->manual_hold_flag) && 
        $this->part->isUnofficial();
        return view('components.part.part-check-message', compact('errors', 'show'));
    }
}
