<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class venderSlot extends Model
{
    protected $table = 'vender_slots';
    protected $fillable = ['vender_id','day','start_time','end_time','max_patient','is_active'];
    
    
    /**
     * Get the user's start time.
     *
     * @param  string  $value
     * @return string
     */
    public function getStartTimeAttribute($value)
    {
        return substr($value, 0, -3);
    }

    /**
     * Get the user's end time.
     *
     * @param  string  $value
     * @return string
     */
    public function getEndTimeAttribute($value)
    {
        return substr($value, 0, -3);
    }

    public function slot() {
        return $this->belongsTo('App\slot', 'slot_id', 'id');
    }
    
    public function slotDayOff() {
        return $this->belongsTo('App\VendorSlotDayBreak', 'vender_id', 'vendor_id');
    }

    public function bookings() {
        return $this->hasMany('App\Booking', 'vendor_slot_id', 'id');
    }

    public function slotDateOff() {
        return $this->hasOne('App\VendorSlotDateBreak', 'vendor_slot_id', 'id');
    }

    public function vender() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
}
