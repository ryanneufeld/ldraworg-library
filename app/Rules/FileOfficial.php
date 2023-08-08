<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Auth;
use App\Models\Part;

class FileOfficial implements DataAwareRule, ValidationRule
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
            $official_exists = !is_null(Part::official()->name($part->name)->first());
            $unofficial_exists = !is_null(Part::unofficial()->name($part->name)->first());
        } else {
            $filename = $value->getClientOriginalName();
            $official_exists = !is_null(Part::official()->where('filename', 'LIKE', "%{$filename}")->first());
            $unofficial_exists = !is_null(Part::unofficial()->where('filename', 'LIKE', "%{$filename}")->first());
        }
        $cannotfix = !Auth::check() || Auth::user()->cannot('part.submit.fix');
        if ($official_exists && !$unofficial_exists && $cannotfix) {
            $fail('partcheck.fix.unofficial')->translate();
        }
        elseif ($official_exists && !$unofficial_exists && (!array_key_exists('officialfix', $this->data) || $this->data['officialfix'] == false)) {
            $fail('partcheck.fix.checked')->translate();
        }  
    }
}
