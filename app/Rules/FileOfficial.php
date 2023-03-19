<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Auth;

use App\Models\PartType;
use App\Models\Part;

class FileOfficial implements DataAwareRule, InvokableRule
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
      $cannotfix = empty(Auth::user()) || Auth::user()->cannot('part.submit.fix');
      $official_exists = !empty(Part::findOfficialByName($pt->folder . $filename));
      $unofficial_exists = !empty(Part::findUnofficialByName($pt->folder . $filename));
      if ($official_exists && !$unofficial_exists && $cannotfix) {
        $fail('partcheck.fix.unofficial')->translate();
      }
      elseif ($official_exists && !$unofficial_exists && empty($this->data['partfix'])) {
        $fail('partcheck.fix.checked')->translate();
      }  
    }
}
