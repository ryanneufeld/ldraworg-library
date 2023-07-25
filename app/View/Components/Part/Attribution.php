<?php

namespace App\View\Components\Part;

use Illuminate\View\Component;

use App\Models\Part;
use App\Models\User;

class Attribution extends Component
{
  /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        protected Part $part
    ) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $editusers = User::where('id', '<>', $this->part->user->id)->where('account_type', '<>', 2)->hasSubmittedPart($this->part)->get();
        $copyuser = $this->part->user;
        return view('components.part.attribution', compact('copyuser', 'editusers'));
    }
}
