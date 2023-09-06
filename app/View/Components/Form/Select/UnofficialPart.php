<?php

namespace App\View\Components\Form\Select;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class UnofficialPart extends Component
{
    /**
     * The properties / methods that should not be exposed to the component template.
     *
     * @var array
     */
    protected $except = ['withDescription'];

    /**
     * Create a new component instance.
     */
    public function __construct(
        public $label = "Unofficial Part",
        public $placeholder = "Part",
        public $withDescription = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if ($this->withDescription) {
            $options = [];
            foreach(\App\Models\Part::unofficial()->orderby('filename')->get() as $part) {
                $options[$part->id] = "{$part->filename} {$part->description}";
            }
        } else {
            $options = \App\Models\Part::unofficial()->orderby('filename')->pluck('filename', 'id')->all();
        }
        return view('components.form.select.unofficial-part', compact('options'));
    }
}
