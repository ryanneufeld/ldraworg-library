<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SelectPageItems extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $options = [50, 100, 250, 500],
        public $label = 'Items per Page'
    ) { }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->options = array_combine($this->options, $this->options);
        return view('components.form.select-page-items');
    }
}
