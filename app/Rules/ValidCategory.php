<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidCategory implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail) {
      if ($value->getMimetype() === 'text/plain') {
        $file = $value->get();
        $desc = FileUtils::getDescription($file);
        $cat = FileUtils::getCategory($file);
        if (!PartCheck::checkCategory($value->get())) {
          $fail('partcheck.category.invalid')->translate(['value' => $cat['category']]);
        }  
        elseif ($cat['category'] == 'Moved' && $desc[0] != '~') {
          $fail('partcheck.category.movedto')->translate();
        }  
      }
    }
}
