<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Http\UploadedFile;

use App\LDraw\PartCheck;
use App\LDraw\FileUtils;

class ValidLDrawFile implements InvokableRule
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
      $filename = basename($value->getClientOriginalName());
      $fileext = $value->getClientOriginalExtension();
      $filemime = $value->getMimetype();
      //Check valid file format
      if ((strtolower($fileext) !== 'dat' &&
           strtolower($fileext) !== 'png') ||
          ($filemime !== 'text/plain' &&
           $filemime !== 'image/png') ||
          (($fileext === 'dat' && $filemime !== 'text/plain') &&
           ($fileext === 'png' && $filemime !== 'image/png'))) {
        $fail('File ' . $filename . ' has an invalid file format');
      }

      if ($filemime === 'text/plain') {
        $file = $value->get();
        $header = FileUtils::getHeader($file);

        $checker = new PartCheck($filename, $header);

        if (!$checker->checkName()) {
          $fail('File ' . $filename . ' has an invalid Name line');
          return;
        }
        if (!$checker->checkAuthor()) {
          $fail('File ' . $filename . ' has an invalid Author line');
          return;
        }
        if (!$checker->checkPartType()) {
          $fail('File ' . $filename . ' has an invalid !LDRAW_ORG line');
          return;
        }
        if (!$checker->checkLicense()) {
          $fail('File ' . $filename . ' has an invalid !LICENSE line');
          return;
        }
        if (!$checker->checkApprovedLibraryLicense()) {
          $fail('File ' . $filename . ' has a !LICENSE that is not approved for the library');
          return;
        }
        if (!$checker->checkBFCCertify()) {
          $fail('File ' . $filename . ' has an invalid BFC CERTIFY line in the header');
          return;
        }
        if (!$checker->checkAuthorInUsers()) {
          $fail('File ' . $filename . ' Author not found');
          return;
        }
        if (!$checker->checkNameAndPartType()) {
          $fail('File ' . $filename . ' Name is invalid for part type');
          return;
        }
      //Check/Validate for Name line
      //Check Part Type
      //Check Licence
      //Check BFC
      //Check Category
      //Check for "Pattern" and Set Keywords
      //Check history
      //Check header order
      }

    }
}
