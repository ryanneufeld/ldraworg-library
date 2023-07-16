<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\DataAwareRule;

use App\Models\PartType;
use App\Models\Part;

class FileReplace implements DataAwareRule, InvokableRule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
      $filename = basename(strtolower($value->getClientOriginalName()));
      $pt = PartType::find($this->data['part_type_id']);
      if (!empty(Part::unofficial()->name($pt->folder . $filename)->first()) && !isset($this->data['replace'])) {
        $fail('partcheck.replace')->translate();
      }  
    }
}
