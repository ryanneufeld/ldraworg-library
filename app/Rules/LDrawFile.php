<?php

namespace App\Rules;

use App\LDraw\FileUtils;
use App\LDraw\PartCheck;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;

class LDrawFile implements DataAwareRule, ValidationRule
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
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fileext = strtolower($value->getClientOriginalExtension());
        $filemime = $value->getMimetype();
        dump($filemime);
        //Check valid file format
        if (!in_array($fileext, ['dat', 'png'])) {
            $fail('partcheck.fileformat')->translate(['attribute' => 'extension', 'value' => $fileext]);
            return;
        } elseif (!in_array($filemime, ['text/plain', 'image/png']) || ($fileext == 'dat' && $filemime != 'text/plain') || ($fileext == 'png' && $filemime != 'image/png')) {
            $fail('partcheck.fileformat')->translate(['attribute' => 'format', 'value' => $fileext]);
            return;
        }

        // PNG ends validation here, otherwise clean file text.
        if ($filemime == 'text/plain') {
            $text = FileUtils::cleanFileText($value->get(), false, true);
        } else {
            $text = '';
        }
        
        $filename = basename($value->getClientOriginalName());
        $name = FileUtils::getName($text);
        if (! PartCheck::checkLibraryApprovedName("0 Name: $filename")) {
            $fail('partcheck.name.invalidchars')->translate();
        } elseif ($filename[0] == 'x') {
            $fail('partcheck.name.xparts')->translate();
        }    
        if ($name && basename(str_replace('\\', '/', $name)) !== $filename) {
            $fail('partcheck.name.mismatch')->translate(['value' => basename($name)]);
        }

        $headerend = FileUtils::headerEndLine($text);
        $text = explode("\n", $text);
        foreach ($text as $index => $line) {
          if (! PartCheck::validLine($line)) {
            $fail('partcheck.line.invalid')->translate(['value' => $index + 1]);
          } elseif (! empty($line) && $index > $headerend && $line[0] == '0' && ! in_array(explode(' ', trim($line))[1], config('ldraw.allowed_metas.body'), true)) {
            $fail('partcheck.line.invalidmeta')->translate(['value' => $index + 1]);
          }
        }
    
    }
}
