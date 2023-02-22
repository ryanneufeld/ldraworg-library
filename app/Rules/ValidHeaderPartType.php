<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\PartType;

class ValidHeaderPartType implements InvokableRule
{
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
        $ftype = FileUtils::getPartType($value);
        $desc = FileUtils::getDescription($value);
        $dtag = empty($desc) ? false : $desc[0];

        $hf = PartType::firstWhere('type', $ftype['type'] ?? '')->folder;

        if (!PartCheck::checkPartType($value)) {
          $fail('partcheck.missing')->translate(['attribute' => '!LDRAW_ORG']);
        }
        elseif (PartType::firstWhere('type', $ftype['type'] ?? '')->folder != request()->part->type->folder) {
          $fail('partcheck.type.change')->translate();
        }
        else {
          if (!PartCheck::checkNameAndPartType($value)) {
            $fail('partcheck.type.path')->translate(['name' => FileUtils::getName($value), 'type' => $ftype['type']]);
          }
          if ($ftype['type'] == 'Subpart' && $dtag != "~") {
            $fail('partcheck.type.subpartdesc')->translate();
          }
          //Check qualifiers
          if ($ftype['qual'] == 'Physical_Color') {
            $fail('partcheck.type.phycolor')->translate();
          }
          elseif ($ftype['qual'] == 'Alias' && $ftype['type'] != 'Shortcut' && $ftype['type'] != 'Part') {
            $fail('partcheck.type.alias')->translate();
          }
          elseif ($ftype['qual'] == 'Alias' && $dtag != '=') {
            $fail('partcheck.type.aliasdesc')->translate();
          }
          elseif ($ftype['qual'] == 'Flexible_Section' && $ftype['type'] != 'Part') {
            $fail('partcheck.type.flex')->translate();
          }
          elseif ($ftype['qual'] == 'Flexible_Section' && !preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}\.dat#', FileUtils::getName($value), $matches)) {
            $fail('partcheck.type.flexname')->translate();
          }
        }  
    }
}
