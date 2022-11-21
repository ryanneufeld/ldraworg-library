<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidLDrawFileType;
use App\Rules\ValidLines;
use App\Rules\ValidName;
use App\Rules\ValidAuthor;
use App\Rules\ValidDescription;
use App\Rules\ValidPartType;
use App\Rules\ValidCategory;
use App\Rules\ValidKeywords;
use App\Rules\ValidHistory;

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
      'replace' => 'boolean',
      'partfix' => 'boolean',
      'comment' => 'nullable|string',
//      'user_id' => 'required|exists:users,id',
      'partfile' => 'required',
      'partfile.*' => ['file', 
                        new ValidLDrawFileType,
                        new ValidName,
                        new ValidDescription,
                        new ValidAuthor,
                        new ValidPartType,
                        new ValidCategory,
                        new ValidKeywords,
                        new ValidHistory,
                        new ValidLines,
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
          foreach(request()->file('partfile') as $file) {
            $partnames[] = basename(strtolower($file->getClientOriginalName()));
          }  
          request()->merge(['partnames' => $partnames]);
        }  
      });
  }
}
