<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidHeaderName implements InvokableRule
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
      $part = request()->part;
      if (!PartCheck::checkName($value)) {
        $fail('partcheck.missing')->translate(['attribute' => 'Name:']);
      }
      elseif (FileUtils::getName($value) != $part->nameString()) {
        $fail('partcheck.name.change')->translate();
      }
    }
}
