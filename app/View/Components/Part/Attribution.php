<?php

namespace App\View\Components\Part;

use App\Models\Part;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\Component;

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
        $editusers = User::where('id', '<>', $this->part->user->id)
            ->whereAll(['is_ptadmin', 'is_synthetic'], false)
            ->whereHas('part_history', fn (Builder $q) => $q->where('part_id', $this->part->id))
            ->get();
        $copyuser = $this->part->user;

        return view('components.part.attribution', compact('copyuser', 'editusers'));
    }
}
