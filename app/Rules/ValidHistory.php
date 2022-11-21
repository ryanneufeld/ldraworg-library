<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\User;

class ValidHistory implements InvokableRule
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
        $history = FileUtils::getHistory($file);
        if (!empty($history)) {
          if (count($history) < mb_substr_count($file, '!HISTORY')) {
            $fail('partcheck.history.invalid')->translate();
          }
          else {
            foreach($history as $hist) {
              if (empty(User::firstWhere('name', $hist['user']) ?? User::firstWhere('realname', $hist['user']))) {
                $fail('partcheck.history.author')->translate(['value' => $hist['user'], 'date' => $hist['date']]);
              }
            }
          }
        }
      }
    }
}
