<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidAuthor implements InvokableRule
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
        if (!PartCheck::checkAuthor($file)) {
          $fail('partcheck.missing')->translate(['attribute' => 'Author:']);
        }
        elseif (!PartCheck::checkAuthorInUsers($file)) {
          $fail('partcheck.author.registered')->translate(['value' => 'Author:']);
        }
      }
    }
}
