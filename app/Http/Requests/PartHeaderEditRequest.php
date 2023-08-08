<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Rules\ValidHeaderDescription;
use App\Rules\ValidHeaderHistory;
use App\Rules\ValidHeaderKeywords;
use App\Rules\ValidHeaderCategory;

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
            'description' => ['required', 'string', new ValidHeaderDescription],
            'part_type_id' => 'nullable|exists:part_types,id',
            'part_type_qualifier_id' => 'nullable|exists:part_type_qualifiers,id',
            'help' => 'nullable|string',
            'keywords' => ['nullable','string', new ValidHeaderKeywords],
            'cmdline' => 'nullable|string',
            'part_category_id' => ['nullable','exists:part_categories,id', new ValidHeaderCategory],
            'history' => ['nullable','string', new ValidHeaderHistory],
            'editcomment' => 'nullable|string',
        ];
    }
}
