<?php

namespace App\Rules;

use App\LDraw\PartCheck;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LDrawFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {        
        if ($errors = PartCheck::checkFile($value)) {
            foreach($errors as $error) {
                if (array_key_exists('args', $error)) {
                    $fail($error['error'])->translate($error['args']);
                } else {
                    $fail($error['error'])->translate();
                }    
            }    
        }
    }
}
