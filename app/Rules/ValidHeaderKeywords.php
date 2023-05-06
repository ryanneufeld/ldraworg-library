<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\LDraw\FileUtils;
use App\LDraw\LDrawFileValidate;

class ValidHeaderKeywords implements DataAwareRule, ValidationRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;
    
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];
 
    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData($data)
    {
        $this->data = $data;
 
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
      $text = "0 {$this->data['description']}\n0 !KEYWORDS $value";
      // Keyword Check
      $keywords = FileUtils::getKeywords($text);
      $isPatternOrSticker = preg_match('#^[a-z0-9_-]+?[pd][a-z0-9]{2,3}\.dat$#i', request()->part->name(), $matches);
      if (request()->part->type->folder == 'parts/' && $isPatternOrSticker) {
        if (empty($keywords)) {
          $fail('partcheck.keywords')->translate();
        } else {
          $setfound = false;
          foreach ($keywords as $word) {
            if (mb_strtolower(explode(' ', trim($word))[0]) == 'set' || mb_strtolower($word) == 'cmf' || mb_strtolower($word) == 'build-a-minifigure') {
              $setfound = true;
              break;
            }
          }
          if (! $setfound) {
              $fail('partcheck.keywords')->translate();
          }
        }
      }
    }
}
