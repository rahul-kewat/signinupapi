<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRefund extends Model
{
    protected $table = 'booking_refund_request';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['booking_id', 'amount','currency','note'];

    public function booking() {
        return $this->belongsTo('App\Booking');
    }
}
