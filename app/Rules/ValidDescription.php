<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidDescription implements InvokableRule
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

        if (!PartCheck::checkDescription($file)) {
          $fail('partcheck.description.missing')->translate();
        }
        elseif (!PartCheck::checkLibraryApprovedDescription($file)) {
          $fail('partcheck.description.invalidchars')->translate();
        }
      }
    }
}
