<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\LDraw\FileUtils;
use App\LDraw\LDrawFileValidate;

class ValidHeaderHistory implements DataAwareRule, ValidationRule
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
    public function setData(array $data): static
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
      if (!is_null($value)) {
        $lines = explode("\n", FileUtils::dos2unix(rtrim($value)));
  
        if (count($lines) != mb_substr_count($value, '0 !HISTORY')) {
          $fail('partcheck.history.invalid')->translate();
          return;
        }  
      }

      $errors = LDrawFileValidate::ValidHistory($value);
      if (!empty($errors)) {
        foreach($errors as $e) {
          $fail($e);
        }
        return;
      }

      $part = request()->part;

      $hist = '';
      foreach ($part->history()->oldest()->get() as $h) {
        $hist .= $h->toString() . "\n";
      }
      $hist = rtrim($hist);
      //dd(!empty($hist) , empty($value), strpos(FileUtils::dos2unix(rtrim($value)), $hist) === false, empty($this->data['editcomment']));
      if (((!empty($hist) && empty($value)) || strpos(FileUtils::dos2unix(rtrim($value)), $hist) === false) && empty($this->data['editcomment'])) 
        $fail('partcheck.history.alter')->translate();
    }
}
