<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use App\Rules\StringLenth;

class RegisterUser extends FormRequest
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
        return [
            'firstname' => 'required|string|regex:/^[a-zA-Z]+$/u|max:45',
            'lastname' => 'required|string||regex:/^[a-zA-Z]+$/u|max:45',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:6',
            'phone_country_code' => 'required',
            'refferal_code' => 'string|max:6|nullable',
            'phone_number' => ['required','integer','digits:10','unique:users','exists:phone_otps,phone_no'],
            'phone_country_code' => 'required',
            'otp' => 'required|integer|exists:phone_otps,otp'
        ];
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
