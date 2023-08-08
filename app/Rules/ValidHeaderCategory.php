<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\Models\PartCategory;

class ValidHeaderCategory implements DataAwareRule, ValidationRule
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
    public function setData($data): static
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
        if(request()->part->type->folder == 'parts/') {
            $c = str_replace(['~','|','=','_'], '', mb_strstr($this->data['description'], " ", true));
            $cat = PartCategory::firstWhere('category', $c);
            if (empty($cat) && empty($value)) {
                $fail('partcheck.category.invalid')->translate(['value' => $c]);
            } 
        }
    }
}
