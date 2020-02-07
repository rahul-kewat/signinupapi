<?php
namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model {

    
    protected $table = 'bookings_details';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['booking_id', 'appointment_for', 'age', 'blood_group', 'contact_number', 'description','patient_name','gender'];

    public function booking() {
        return $this->belongsTo('App\Booking');
    }

}
