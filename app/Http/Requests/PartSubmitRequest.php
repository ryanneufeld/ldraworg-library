<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PartSubmitRequest extends FormRequest
{
    protected $stopOnFirstFailure = false;
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
    public function authorize(): bool
    {
        return true;
    }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
    public function rules(): array
    {
        return [
            'comments' => 'nullable|string',
            'proxy_user_id' => ['nullable', 'exists:users,id', new \App\Rules\ProxySubmit()],
            'partfiles' => 'required',
            'partfiles.*' => [
                'file',
                'mimetypes:text/plain,image/png',
                new \App\Rules\LDrawFile(),
                new \App\Rules\FileReplace(),
                new \App\Rules\FileOfficial(),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->has('partfiles')) {
                    $partnames = [];
                    foreach($this->partfiles as $index => $file) {
                        $partnames[$index] = strtolower($file->getClientOriginalName());
                    }
                    $validator->errors()->add('partnames',$partnames);
                }  
            }
        ];
    }
}
