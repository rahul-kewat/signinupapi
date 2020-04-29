<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use App\Rules\StringLenth;

class Login extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        
        $rules = [];
        $rules['phone_number'] = 'required_without:email|integer';
        $rules['phone_country_code'] = 'required_with:phone_number';
        $rules['email'] = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'required_without:phone_number|string|email|exists:users,email' :  ['required_without:phone_number','string','exists:users,phone_number'];
        $rules['password'] = 'required|string';
        return $rules;
    }

    /**
     * Overwrite Validation error response
     * 
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = new JsonResponse([ 
            'status' => 0, 
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
