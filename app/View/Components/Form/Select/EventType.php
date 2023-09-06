<?php

namespace App\View\Components\Form\Select;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EventType extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $label = "Type",
        public string $placeholder = "Part Event Type",
        public string $selected = ''
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $options = \App\Models\PartEventType::pluck('name', 'id')->all();
        return view('components.form.select.event-type', compact('options'));
    }
}
