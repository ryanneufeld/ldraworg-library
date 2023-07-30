<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\Models\PartType;
use App\Models\Part;

class FileReplace implements DataAwareRule, ValidationRule
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
    public function setData($data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value->getMimeType() == 'text/plain') {
            $part = app(\App\LDraw\Parse\Parser::class)->parse($value->get());
            $unofficial_exists = !is_null(Part::unofficial()->name($part->name)->first());
        } else {
            $filename = $value->getClientOriginalName();
            $unofficial_exists = !is_null(Part::unofficial()->where('filename', 'LIKE', "%{$filename}")->first());
        }
        if ($unofficial_exists && $this->data['replace'] !== true) {
            $fail('partcheck.replace')->translate();
        }  
    }
}
