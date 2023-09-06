<?php

namespace App\View\Components\Form\Select;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PartType extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $label = "Type",
        public $placeholder = "Part Type",
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $options = \App\Models\PartType::pluck('name', 'id')->all();
        return view('components.form.select.part-type', compact('options'));
    }
}
