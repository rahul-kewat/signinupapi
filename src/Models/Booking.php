<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Booking"))
 */
class Booking extends Model {

    /**
     * @var string
     * @SWG\Property(
     *   property="vender_id",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="service_id",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="vendor_slot_id",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="price",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="booking_date",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="card_source_id",
     *   type="string" 
     * )
     */

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bookings';
    
    const active = 1;
    const cancel = 2;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'vender_id', 'service_id', 'vendor_slot_id', 'price', 'booking_date', 'booking_start', 'booking_end','reference_id'];

    public function bookingReports() {
        return $this->hasMany('App\BookingReports','booking_id','id');
    }

    public function bookingDetail() {
        return $this->hasOne('App\BookingDetail','booking_id','id');
    }

    public function bookingDoctorReports() {
        return $this->hasOne('App\BookingDoctorReport','booking_id','id');
    }

    public function vendor() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }

    public function service() {
        return $this->belongsTo('App\VenderService','service_id','id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    /**
     * Get the user's appointment_time.
     *
     * @param  string  $value
     * @return string
     */
    public function getAppointmentTimeAttribute()
    {
        return "{$this->booking_date} {$this->booking_start}";
    }

    /***********************Working relations**********************************/

    public function scopeActive($query) {
        return $query->whereStatus('1');
    }

    public function scopeUserBy($query, $id) {
        return $query->whereUserId($id);
    }


    public function vender_services() {
        return $this->belongsTo('App\VenderService','service_id', 'id');
    }

    public function ExtraHour() {
        return $this->hasMany('App\ExtraHour');
    }

    public function review() {
        return $this->hasMany('App\Review');
    }
       
    public function status() {
        return $this->belongsTo('App\Status');
    }

    public function slot()
    {
        return $this->belongsTo('App\slot');
    }

}
