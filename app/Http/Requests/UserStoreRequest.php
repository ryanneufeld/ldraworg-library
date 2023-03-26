<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
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
      if (empty($this->route('user'))) {
        $unique = Rule::unique('users');
      }
      else {
        $unique = Rule::unique('users')->ignore($this->route('user')->id);
      }
      return [
        'realname' => ['required', 'string', $unique],
        'name' => ['required', 'string', $unique],
        'email' => ['required', 'email',$unique],
        'roles' => 'required',
        'part_license_id' => 'required|exists:part_licenses,id'
      ];
    }
}
