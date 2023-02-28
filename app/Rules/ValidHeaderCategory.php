<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidHeaderCategory implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
      $desc = FileUtils::getDescription($value);
      $type = FileUtils::getPartType($value);
      $cat = FileUtils::getCategory($value);
      if (($type == 'Part' || $type == 'Shortcut') && !PartCheck::checkCategory($value)) {
        $fail('partcheck.category.invalid')->translate(['value' => $cat['category']]);
      }  
      elseif ($cat['category'] == 'Moved' && $desc[0] != '~') {
        $fail('partcheck.category.movedto')->translate();
      }  
    }
}
