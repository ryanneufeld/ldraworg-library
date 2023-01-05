<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

class ValidLDrawFileType implements InvokableRule
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
      $fileext = strtolower($value->getClientOriginalExtension());
      $filemime = $value->getMimetype();
      //Check valid file format
      if ($fileext !== 'dat' && $fileext !== 'png') {
        $fail('partcheck.fileformat')->translate(['attribute' => 'extension', 'value' => $fileext]);
      }
      if (($filemime !== 'text/plain' &&
           $filemime !== 'image/png') ||
          (($fileext === 'dat' && $filemime !== 'text/plain') &&
           ($fileext === 'png' && $filemime !== 'image/png'))) {
        $fail('partcheck.fileformat')->translate(['attribute' => 'format', 'value' => $fileext]);
      }
    }
}
