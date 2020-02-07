<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';
    
    protected $fillable = ['status_type', 'label'];
    
    public function booking()
    {
        return $this->hasMany('App\Booking');
    }
    
    public function bookingStatusHistory()
    {
        return $this->hasMany('App\BookingStatusHistory');
    }
}
