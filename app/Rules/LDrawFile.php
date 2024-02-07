<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

class LDrawFile implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];
 
    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
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
        if ($value->getMimeType() !== 'text/plain') {
            return;
        }
        $part = app(\App\LDraw\Parse\Parser::class)->parse($value->get());
        $errors = app(\App\LDraw\Check\PartChecker::class)->check($part);
        foreach($errors ?? [] as $error) {
            $fail($error);
        }
        dd($errors);    
    }
}
