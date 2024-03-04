<?php

namespace App\View\Components\Message;

use App\Models\Part;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PartReleasable extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Part $part,
        protected \App\LDraw\Check\PartChecker $checker
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $check = $this->checker->checkCanRelease($this->part);
        return view('components.message.part-releaseable', compact('check'));
    }
}
