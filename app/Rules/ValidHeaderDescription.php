<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\LDraw\PartCheck;

class ValidHeaderDescription implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
      if (! PartCheck::checkLibraryApprovedDescription("0 $value")) {
        $fail('partcheck.description.invalidchars')->translate();
      }

      $isPattern = preg_match('#^[a-z0-9_-]+?p[a-z0-9]{2,3}\.dat$#i', request()->part->name(), $matches);
      $hasPatternText = preg_match('#^.*?\sPattern(\s\((Obsolete|Needs Work)\))?$#ui', $value, $matches);
      if (request()->part->type->folder == 'parts/' && $isPattern && !$hasPatternText) {
          $fail('partcheck.description.patternword')->translate();
      }
}
}
