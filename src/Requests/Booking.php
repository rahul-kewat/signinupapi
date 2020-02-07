<?php

namespace Devrahul\Signinupapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use App\Rules\VendorSlotCheck;
use App\Rules\VendorServiceCheck;
use App\Rules\ReportRule;
use App\Rules\SlotAvailabityCheck;
use Illuminate\Http\Request;

class Booking extends FormRequest
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
            'vender_id' => 'required|exists:users,id',
            'service_id' => ['required','exists:vender_services,id',new VendorServiceCheck(Request::get('vender_id'))],
            'vendor_slot_id' => ['required','exists:vender_slots,id',new VendorSlotCheck(Request::get('vender_id')),new SlotAvailabityCheck(Request::get('vender_id'),Request::get('service_id'),Request::get('booking_date'))],
            'price' =>  'required',
            'booking_date' => 'required|date|after_or_equal:today',
            'card_source_id' => 'required',
            'appointment_for' => 'required',
            'age' => 'required',
            'blood_group' => 'required',
            'contact_number' => 'required',
            'description' => 'required',
            'patient_name' => 'required|string',
            'report_ids' => ['sometimes',new ReportRule()],
            'currency' => 'required',
            'original_amount' => 'required',
            'gender' => 'required',
            'card_id' => 'required'
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
