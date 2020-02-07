<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\User;

class DocterRole implements Rule
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
        $user = User::find($value);
        if(!$user){
            return false;
        }
        $role = $user->roles->first()->id;
        return $role === 2;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('api/validation.vendor_must_doctor');
    }
}
