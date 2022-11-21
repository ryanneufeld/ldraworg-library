<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidLicense implements InvokableRule
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
      if ($value->getMimetype() === 'text/plain')
        if (!PartCheck::checkLicense($value->get())) {
          $fail('partcheck.missing')->translate(['attribute' => '!LICENSE']);
        }  
        elseif (!PartCheck::checkLibraryApprovedLicense($value->get())) {
          $fail('partcheck.license.approved')->translate();
        }  
      }
    }
}
