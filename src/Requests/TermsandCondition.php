<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

class TermsandCondition extends FormRequest
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
            'language' => 'required|string'
        ];
    }

    /**
    * Add parameters to be validated
    *
    * @return array
    */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['language'] = $this->header('language');
        return $data;
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
