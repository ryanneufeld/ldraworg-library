<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\LDraw\PartCheck;

class LDrawHeader implements DataAwareRule, ValidationRule
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
        if ($errors = PartCheck::checkHeader($value, $this->data)) {
            foreach($errors as $error) {
                if (array_key_exists('args', $error)) {
                    $fail($error['error'])->translate($error['args']);
                } else {
                    $fail($error['error'])->translate();
                }    
            }    
        }
    }
}
