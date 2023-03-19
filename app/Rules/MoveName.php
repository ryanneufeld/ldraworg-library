<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Storage;

use App\Models\PartType;
use App\Models\Part;

class MoveName implements InvokableRule, DataAwareRule
{
  /**
   * Indicates whether the rule should be implicit.
   *
   * @var bool
   */
  public $implicit = true;

  protected $data = [];

  public function setData($data) {
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
  public function __invoke($attribute, $value, $fail) {
    $pt = PartType::find($this->data['part_type_id']);
    $p = Part::find($this->data['part_id']);
    $fname = !empty($value) ? $value : basename($p->filename);
    $fname = $pt->folder . $fname;
    if (!empty(Part::firstWhere('filename', $fname)))  {
      $fail($fname . " already exists");
    }      
  }
}
