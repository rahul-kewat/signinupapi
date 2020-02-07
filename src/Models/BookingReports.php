<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReports extends Model
{
    protected $table = 'booking_reports';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['booking_id', 'report_id','type'];


    public function reports() {
        return $this->belongsTo('App\Media','report_id','id');
    }
}
