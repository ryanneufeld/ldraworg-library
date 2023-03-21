<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\VoteType;

class VoteRequest extends FormRequest
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
      $vts = array_merge(VoteType::all()->pluck('code')->all(),['N','M']);
      return [
        'vote_type' => ['required' , Rule::in($vts)],
        'comment' => 'required_if:vote_type,M,H|nullable|string',
      ];
    }
}
