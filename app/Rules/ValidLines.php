<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Log;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\Models\PartType;

class ValidLines implements DataAwareRule, InvokableRule
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
      if ($value->getMimetype() === 'text/plain') {
        $file = FileUtils::cleanFileText($value->get());
        $headerend = FileUtils::headerEndLine($file);
        $file = explode("\n", $file);
        foreach ($file as $index => $line) {
          if (!PartCheck::validLine($line)) {
            $fail('partcheck.line.invalid')->translate(['value' => $index + 1]);
          }
          elseif (!empty($line) && $index > $headerend && $line[0] === 0 && !in_array(strtok(mb_substr($line, 1), " "), self::$allowed_body_metas, true)) {
            $fail('partcheck.line.invalidmeta')->translate(['value' => $index + 1]);
          }  
        }  
      }
    }
}
