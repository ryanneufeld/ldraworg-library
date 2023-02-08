<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\User;

class ValidHeaderHistory implements InvokableRule
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
      $history = FileUtils::getHistory($value);
      $part = request()->part;
      $histcount = empty($history) ? 0 : count($history); 
      if ($histcount < $part->history()->count()) {
        $fail('partcheck.history.removed')->translate();
      }
      if (!empty($history)) {
        if (count($history) < mb_substr_count($value, '!HISTORY')) {
          $fail('partcheck.history.invalid')->translate();
        }
        else {
          $usererr = false;
          foreach($history as $hist) {
            if (empty(User::findByName($hist['user'], $hist['user']))) {
              $fail('partcheck.history.author')->translate(['value' => $hist['user'], 'date' => $hist['date']]);
              $usererr = true;
            }
          }
          /*
          if (!$usererr) {
            foreach($part->history as $phist) {
              $found = false;
              foreach($history as $hist) {
                $d = new \DateTime($phist->created_at);
                if (User::findByName($hist['user'], $hist['user'])->id == $phist->user->id &&
                    $d->format('Y-m-d') == $hist['date']) {
                  $found = true;
                  break;
                }                      
              }
              if (!$found) {
                $fail('partcheck.history.alter')->translate();
                break;
              }  
            }
          }
          */
        }
      }
    }
}
