<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class SocialRequest extends FormRequest
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
            'email' => 'required|string|email',
            'type'  => 'required|string|in:facebook,google',
            'social_id'    => 'required|string',
            'firstname'    => 'sometimes|required|string',
            'phone_country_code'    => 'sometimes|required|string',
            'phone_number'    => 'sometimes|required|integer|digits:10',
            'social_image'    => 'sometimes|required|string'
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
