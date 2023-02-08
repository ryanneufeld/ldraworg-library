<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidLDrawFile;
use App\Rules\FileReplace;
use App\Rules\FileOfficial;
use App\Rules\ProxySubmit;

class PartSubmitRequest extends FormRequest
{
  protected $stopOnFirstFailure = false;
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
      return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
  public function rules()
  {
    return [
      'part_type_id' => 'required|exists:part_types,id',
      'comment' => 'nullable|string',
      'user_id' => ['required', 'exists:users,id', new ProxySubmit],
      'partfile' => 'required',
      'partfile.*' => [
        'file', 
        new ValidLDrawFile,
        new FileReplace,
        new FileOfficial,
      ],
    ];
  }

  /**
   * Configure the validator instance.
   *
   * @param  \Illuminate\Validation\Validator  $validator
   * @return void
   */
  public function withValidator($validator)
  {
      $validator->after(function ($validator) {
        if (request()->hasFile('partfile')) {
          $partnames = [];
          foreach(request()->file('partfile') as $index => $file) {
            $partnames["partfile.$index"] = basename(strtolower($file->getClientOriginalName()));
          }  
          request()->merge(['partnames' => $partnames]);
        }  
      });
  }
}
