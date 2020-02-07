<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraHour extends Model
{
    protected $table = 'extra_booking_hours';
    protected $fillable = ['booking_id', 'extended_minutes', 'status', 'extention_accepted_at'];
    
    public function booking() {
        return $this->belongsTo('App\Booking');
    }
}
