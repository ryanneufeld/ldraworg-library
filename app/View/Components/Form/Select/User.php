<?php

namespace App\View\Components\Form\Select;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class User extends Component
{
    /**
     * The properties / methods that should not be exposed to the component template.
     *
     * @var array
     */
    protected $except = ['unofficial'];

    /**
     * Create a new component instance.
     */
    public function __construct(
        public $unofficial = null,
        public $label = "Users:",
        public $placeholder = "Users",
    )
    { }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $options = \App\Models\User::whereHas('parts', function ($query) {
            if ($this->unofficial === false) {
              $query->unofficial();
            } elseif ($this->unofficial === true) {
              $query->official();
            }
        })->orderBy('name')->pluck('name', 'id')->all();        
        return view('components.form.select.user', compact('options'));
    }
}
