<?php

namespace Devrahul\Signinupapi\Rules;


use Illuminate\Contracts\Validation\Rule;

class StringLenth implements Rule
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
        //print_r(strlen(trim($value, ' '))); exit;
        return strlen(trim($value, ' ')) === 10;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be 10 numbers.';
    }
}
