<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use App\LDraw\LDrawFileValidate;

class ValidHeaderDescription implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void {
      $type = request()->part->typeString();
      $text = "0 $value\n$type";
      $error = LDrawFileValidate::ValidDescription($text);
      if (!empty($error)) $fail($error[0]);      
    }
}
