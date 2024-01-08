<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\LDraw\Parse\Parser;
use App\Models\PartCategory;
use App\Models\PartType;
use App\Models\Part;

class MoveName implements ValidationRule, DataAwareRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    protected $data = [];

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
        if (!empty($this->data['part_type_id'])) {
            $pt = PartType::find($this->data['part_type_id']);
            $p = Part::find(request()->part->id);
            if (!is_null($pt) && !is_null($p)) {
                $c = app(Parser::class)->getDescriptionCategory($p->header);
                $fname = !empty($value) ? $value : basename($p->filename);
                $fname = $pt->folder . $fname;
                if (!empty(Part::firstWhere('filename', $fname)))  {
                    $fail($fname . " already exists");
                }
                elseif (
                    $p->type->folder !== 'parts/' && 
                    $pt->folder == 'parts/' && 
                    is_null(PartCategory::firstWhere('category', $c))
                ) {
                    $c = 
                    $fail("Moving a part to the parts folder requires a valid category in the description, found {$c}");
                }
            }    
        }
    }
}
