<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidHeaderKeywords implements InvokableRule
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
      $name = FileUtils::getName($value);
      $keywords = FileUtils::getKeywords($value);
      $isPattern = (substr($name, strrpos($name, '.dat') - 3, 1) == 'p' || substr($name, strrpos($name, '.dat') - 2, 1) == 'p');
      if ($isPattern) {
        if (empty($keywords)) {
          $fail('partcheck.keywords')->translate();
        }
        else {
          foreach ($keywords as $word) {
            if (strtolower(strtok($word, " ")) == 'set') return;
          }
          $fail('keywords')->translate();
        }  
      }        
    }
}
