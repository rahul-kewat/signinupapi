<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Auth;
use App\Booking;

class UpdateStatus implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user_id = Auth::user()->id;
        $booking = Booking::find($value);
        if($booking && $user_id == $booking->user_id){
            return true;
        }
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('api/validation.booking_user_check');
    }
}
