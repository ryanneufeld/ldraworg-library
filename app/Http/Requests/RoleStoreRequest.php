<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
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
        if (empty($this->role)) {
            $unique = Rule::unique('users')->ignore($this->role->id);
        }
        else {
            $unique = Rule::unique('users');
        }
        return [
            'name' => ['required', 'string', $unique],
            'permissions.*' => 'nullable|exists:permissions,name',
        ];
    }
}
