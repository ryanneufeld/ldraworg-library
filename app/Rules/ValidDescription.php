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
        $name = strtolower(FileUtils::getName($file));
        $desc = FileUtils::getDescription($file);
        if (!PartCheck::checkDescription($file)) {
          $fail('partcheck.description.missing')->translate();
        }
        elseif (!PartCheck::checkLibraryApprovedDescription($file)) {
          $fail('partcheck.description.invalidchars')->translate();
        }
        elseif ((substr($name, strrpos($name, '.dat') - 3, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'p') && substr($desc, strrpos($desc, ' ') + 1) != 'Pattern') {
          $fail('partcheck.description.patternword')->translate();
        }  
      }
    }
}
