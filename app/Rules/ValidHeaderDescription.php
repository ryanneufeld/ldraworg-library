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
        if (! app(\App\LDraw\Check\PartChecker::class)->checkLibraryApprovedDescription("0 $value")) {
            $fail('partcheck.description.invalidchars')->translate();
        }

        if (
            request()->part->type->folder == 'parts/' && 
            ! app(\App\LDraw\Check\PartChecker::class)->checkDescriptionForPatternText(request()->part->name(), $value)
        ) {
            $fail('partcheck.description.patternword')->translate();
        }
    }
}
