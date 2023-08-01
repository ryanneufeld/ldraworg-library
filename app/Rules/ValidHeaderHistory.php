<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\LDraw\Parse\Parser;
use App\Models\User;

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
        $value = Parser::dos2unix(trim($value));
        if (!is_null($value)) {
            $lines = explode("\n", $value);
            if (count($lines) != mb_substr_count($value, '0 !HISTORY')) {
                $fail('partcheck.history.invalid')->translate();
                return;
            }  

            $history = app(\App\LDraw\Parse\Parser::class)->getHistory($value);
            if (! is_null($history)) {
                foreach ($history as $hist) {
                    if (is_null(User::fromAuthor($hist['user'])->first())) {
                        $fail('partcheck.history.author')->translate();
                    }
                }
            }
        }
        
        $part = request()->part;

        $hist = '';
        foreach ($part->history()->oldest()->get() as $h) {
            $hist .= $h->toString() . "\n";
        }
        $hist = Parser::dos2unix(trim($hist));
        if (((!empty($hist) && empty($value)) || $hist !== $value) && empty($this->data['editcomment'])) {
            $fail('partcheck.history.alter')->translate();
        }
    }
}
