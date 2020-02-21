<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Devrahul\Signinupapi\Rules\StringLenth;

class EditUserProfile extends FormRequest
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
        $phoneNoRules = $this->has('otp') ? ['required','integer','digits:10','exists:phone_otps,phone_no'] : ['required','integer','digits:10'];

        return [
            'first_name' => 'required|string|max:45',
            'last_name' => 'required|string|max:45',
            'phone_country_code' => 'required',
            'phone_number' => $phoneNoRules,
            'gender' => 'required|integer|between:0,2',
            'otp' => 'sometimes|required|string|exists:phone_otps,otp',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'bio' => 'required|string'
            
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
