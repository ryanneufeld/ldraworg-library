<?php

namespace App\View\Components\Form\Radio;

use App\Models\PartType;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PartTypeMove extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $value = '',
        protected string $format = 'dat'
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $options = [];
        foreach (PartType::where('format', $this->format)->pluck('folder', 'id')->unique() as $id => $option) {
            $types = implode(', ', PartType::where('folder', $option)->pluck('name')->all());
            $options[] = ['id' => $id, 'folder' => $option, 'text' => "{$option} ({$types})"]; 
        }
        return view('components.form.radio.part-type-move', compact('options'));
    }
}
