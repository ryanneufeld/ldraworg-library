<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

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
        $keywords = "0 !KEYWORDS " . str_replace(["\n","\r"], [', ',''], $value);
        $keywords = app(\App\LDraw\Parse\Parser::class)->getKeywords($keywords) ?? [];
        if (
            request()->part->type->folder == 'parts/' && 
            ! app(\App\LDraw\Check\PartChecker::class)->checkPatternForSetKeyword(request()->part->name(), $keywords)
        ) {
            $fail('partcheck.keywords')->translate();
        }
    }
}
