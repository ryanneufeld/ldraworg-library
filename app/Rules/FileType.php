<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FileType implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $filename = basename(strtolower($value->getClientOriginalName()));
        $fileext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $type = $value->getMimeType();
        //Check valid file format
        if (!in_array($fileext, ['dat', 'png'])) {
            $fail('partcheck.fileformat', ['attribute' => 'extension', 'value' => $fileext]);
        } elseif (!in_array($type, ['text/plain', 'image/png']) || ($fileext == 'dat' && $type != 'text/plain') || ($fileext == 'png' && $type != 'image/png')) {
            $fail('partcheck.fileformat', ['attribute' => 'format', 'value' => $fileext] );
        }
      }
}
