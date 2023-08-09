<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectEventType extends Component
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
        $options = \App\Models\PartEventType::pluck('name', 'id')->all();
        return view('components.form.select-event-type', compact('options'));
    }
}
