<?php

namespace App\View\Components\Part;

use App\Models\Part;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\LDraw\PartCheck;

class PartCheckMessage extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Part $part
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $errors = [];
        if ($ferrors = PartCheck::checkFile($this->part->toUploadedFile())) {
          foreach($ferrors as $error) {
              if (array_key_exists('args', $error)) {
                $errors[] = __($error['error'], $error['args']);
              } else {
                $errors[] = __($error['error']);;
              }    
          }    
        }
        if ($herrors = PartCheck::checkHeader($this->part->toUploadedFile(), ['part_type_id' => $this->part->part_type_id])) {
          foreach($herrors as $error) {
              if (array_key_exists('args', $error)) {
                $errors[] = __($error['error'], $error['args']);
              } else {
                $errors[] = __($error['error']);;
              }    
          }    
        }
        $show = (!empty($errors) || !$this->part->releasable() || $this->part->vote_summary['S'] != 0) && $this->part->isUnofficial();
        return view('components.part.part-check-message', compact('errors', 'show'));
    }
}
