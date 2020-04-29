<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Devrahul\Signinupapi\Rules\StringLenth;

class SendOtp extends FormRequest
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
        $phoneNoRules = $this->has('email') ? ['required','integer','unique:users,phone_number'] : $this->has('update_profile') ?  ['required','integer','unique:users,phone_number'] : ['required','integer','unique:users,phone_number'];
        

        return [
            'phone_number' => $phoneNoRules,
            'phone_country_code' => 'required|integer',
            'email' => 'sometimes|email|unique:users,email'
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
