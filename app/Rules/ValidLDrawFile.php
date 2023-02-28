<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;
use App\LDraw\LDrawFileValidate;

class ValidLDrawFile implements DataAwareRule, InvokableRule
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
    public function __invoke($attribute, $value, $fail)
    {
      $fileext = strtolower($value->getClientOriginalExtension());
      $filemime = $value->getMimetype();
      
      //Check valid file format
      if ($fileext !== 'dat' && $fileext !== 'png') {
        $fail('partcheck.fileformat')->translate(['attribute' => 'extension', 'value' => $fileext]);
        return;
      }
      if (($filemime !== 'text/plain' &&
           $filemime !== 'image/png') ||
          (($fileext === 'dat' && $filemime !== 'text/plain') &&
           ($fileext === 'png' && $filemime !== 'image/png'))) {
        $fail('partcheck.fileformat')->translate(['attribute' => 'format', 'value' => $fileext]);
        return;
      }
      
      $text = $filemime == 'text/plain' ? FileUtils::cleanFileText($value->get()) : '';
      $errors = LDrawFileValidate::ValidName($text, $value->getClientOriginalName(), $this->data['part_type_id']);
      
      // These checks are only valid for non-texmaps
      if ($filemime == 'text/plain') {
        $errors = array_merge($errors, LDrawFileValidate::ValidDescription($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidAuthor($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidPartType($text, $this->data['part_type_id']));
        $errors = array_merge($errors, LDrawFileValidate::ValidCategory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidKeywords($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidHistory($text));
        $errors = array_merge($errors, LDrawFileValidate::ValidLines($text));      
      }
      
      foreach ($errors as $error) {
        $fail($error);
      }
    }
}
