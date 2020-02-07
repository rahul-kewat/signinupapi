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
        $phoneNoRules = $this->has('email') ? ['required','string','unique:users,phone_number',new StringLenth] : $this->has('update_profile') ?  ['required','string','unique:users,phone_number',new StringLenth] : ['required','string',new StringLenth];
        

        return [
            'phone_number' => $phoneNoRules,
            'phone_country_code' => 'required',
            'email' => 'sometimes|required|email|unique:users,email'
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
