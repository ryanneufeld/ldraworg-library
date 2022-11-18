<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Log;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\PartType;

class ValidName implements DataAwareRule, InvokableRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];
 
    // ...
 
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
      $type = PartType::find($this->data['part_type_id']);
      if(!PartCheck::checkLibraryApprovedName("0 Name: $filename")) {
        $fail('partcheck.name.invalidchars')->translate();
      }
      if ($value->getMimetype() === 'text/plain') {
        $file = $value->get();
        $name = str_replace('\\','/', FileUtils::getName($file));

        if (!PartCheck::checkName($file)) {
          $fail('partcheck.missing')->translate(['attribute' => 'Name:']);
        }
        elseif (basename($name) !== $filename) {
          $fail('partcheck.name.mismatch')->translate(['value' => basename($name)]);
        }
        elseif (!empty($type) && ('parts/' . $name !== $type->folder . basename($name)) && ('p/' . $name !== $type->folder . basename($name))) {
          $fail('partcheck.folder')->translate(['attrubute' => 'Name:', 'value' => $name, 'folder' => $type->folder]);
        }
      }
    }
}
