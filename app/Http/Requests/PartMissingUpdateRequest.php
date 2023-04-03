<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PartMissingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
      return [
        'part_id' => 'required|in:' . $this->missingpart->id,
        'new_part_id' => 'required|exists:parts,id',
      ];
    }
}
