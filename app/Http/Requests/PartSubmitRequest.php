<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class PartSubmitRequest extends FormRequest
{
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
      'filetype' => [
        'required', 
        Rule::in(['part', 'subpart', 'primitive', 'lores', 'hires', 'part_texmap', 'subpart_texmap', 'primitve_texmap'])
      ],
      'replace' => 'boolean',
      'partfix' => 'boolean',
      'comment' => 'nullable|string',
      'user' => 'required|exists:users',
      'partfile' => ['required'],
      'partfile.*' => ['file|'],
    ];
  }
}
