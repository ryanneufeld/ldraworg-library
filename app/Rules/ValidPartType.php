<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\PartType;

class ValidPartType implements DataAwareRule, InvokableRule
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
    public function __invoke($attribute, $value, $fail) {
      $filename = basename(strtolower($value->getClientOriginalName()));
      if ($value->getMimetype() === 'text/plain') {
        $file = $value->get();

        $ftype = FileUtils::getPartType($file);
        $part_type = PartType::firstWhere('type', $ftype['type'] ?? '');
        Log::debug($part_type->folder);
        $name = str_replace('\\','/', FileUtils::getName($file));
        $desc = FileUtils::getDescription($file);
        $dtag = empty($desc) ? false : $desc[0];
        
        if (!PartCheck::checkPartType($file)) {
          $fail('partcheck.missing')->translate(['attribute' => '!LDRAW_ORG']);
        }
        elseif (!PartCheck::checkNameAndPartType($file)) {
          $fail('partcheck.type.path')->translate(['name' => $name, 'type' => $ftype]);
        }
        elseif (!empty($part_type) && $this->data['part_type_id'] != $part_type->id) {
          $fail('partcheck.folder')->translate(['attribute' => '!LDRAW_ORG', 'value' => $ftype['type'], 'folder' => $part_type->folder]);
        }
        elseif ($ftype['type'] == 'Subpart' && $dtag != "~") {
          $fail('partcheck.type.subpartdesc')->translate();
        }
        elseif ($ftype['qual'] == 'Physical_Color') {
          $fail('partcheck.type.phycolor')->translate();
        }
        elseif ($ftype['qual'] == 'Alias' && ($ftype['type'] != 'Shortcut' || $ftype['type'] != 'Part')) {
          $fail('partcheck.type.alias')->translate();
        }
        elseif ($ftype['qual'] == 'Alias' && $dtag != '=') {
          $fail('partcheck.type.aliasdesc')->translate();
        }
        elseif ($ftype['qual'] == 'Flexible_Section' && $ftype['type'] != 'Part') {
          $fail('partcheck.type.flex')->translate();
        }
        elseif ($ftype['qual'] == 'Flexible_Section' && !preg_match('#^[a-z0-9_-]+?k[a-z0-9]{2}\.dat#', $name, $matches)) {
          $fail('partcheck.type.flexname')->translate();
        }
      }
    }
}
