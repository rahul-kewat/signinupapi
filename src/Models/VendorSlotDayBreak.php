<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSlotDayBreak extends Model
{
    protected $table = 'vendor_slots_day_breaks';
     
    protected $fillable = ['vendor_id','day'];
}
