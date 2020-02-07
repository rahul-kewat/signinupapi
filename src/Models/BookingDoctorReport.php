<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BookingDoctorReport extends Model
{
    protected $table = 'booking_doctor_reports';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['booking_id', 'data'];

    
    /**
     * Get the user's data.
     *
     * @param  string  $value
     * @return string
     */
    public function getdataAttribute($value)
    {
        return json_decode(Crypt::decryptString($value));
    }

    public function booking() {
        return $this->belongsTo('App\Booking','booking_id','id');
    }
}
