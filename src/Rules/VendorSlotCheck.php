<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\venderSlot as VendorSlot;

class VendorSlotCheck implements Rule
{

    protected $vender_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($vender_id)
    {
        $this->vender_id = $vender_id;
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
        $vendorSlot = VendorSlot::find($value);
        $vendorId = $vendorSlot ? $vendorSlot->vender_id  : 0 ;
        return $vendorId  == $this->vender_id;
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
