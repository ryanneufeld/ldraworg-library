<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Rules\ValidHeaderName;
use App\Rules\ValidHeaderAuthor;
use App\Rules\ValidHeaderDescription;
use App\Rules\ValidHeaderPartType;
use App\Rules\ValidHeaderCategory;
use App\Rules\ValidHeaderKeywords;
use App\Rules\ValidHeaderHistory;
use App\Rules\ValidHeaderLicense;

class PartHeaderEditRequest extends FormRequest
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
      'h' => [
        'required',
        'string',
        new ValidHeaderName,
        new ValidHeaderDescription,
        new ValidHeaderAuthor,
        new ValidHeaderPartType,
        new ValidHeaderCategory,
        new ValidHeaderKeywords,
        new ValidHeaderHistory,
        new ValidHeaderLicense,
      ],
    ];
  }
}
