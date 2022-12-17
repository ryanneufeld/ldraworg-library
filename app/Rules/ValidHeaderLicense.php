<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;

class ValidHeaderLicense implements InvokableRule
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
      if (!PartCheck::checkLicense($value)) {
        $fail('partcheck.missing')->translate(['attribute' => '!LICENSE']);
      }  
      elseif (!PartCheck::checkLibraryApprovedLicense($value)) {
        $fail('partcheck.license.approved')->translate();
      }  
    }
}
