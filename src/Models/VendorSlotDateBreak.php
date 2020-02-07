<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSlotDateBreak extends Model
{
    protected $table = 'vendor_slots_date_breaks';
     
    protected $fillable = ['vedor_id','vendor_slot_id','date'];
}
