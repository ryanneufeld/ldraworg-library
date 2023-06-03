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
        $show = (!empty($errors) || (!$this->part->hasCertifiedParent() && $this->part->vote_sort == 1 && $this->part->type->folder != "parts/" && !is_null($this->part->official_part_id)) || $this->part->hasUncertifiedSubfiles()) && $this->part->isUnofficial();
        return view('components.part.part-check-message', compact('errors', 'show'));
    }
}
