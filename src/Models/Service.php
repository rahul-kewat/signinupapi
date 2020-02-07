<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
   
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'price', 'price_type', 'status', 'image', 'parent_id', 'cat_id'];

    public function scopeActive($query) {
        return $query->whereStatus('1');
    }

    public function schedule() {
        return $this->hasMany('App\Schedule');
    }

    public function booking() {
        return $this->hasMany('App\Booking');
    }

    public function vendorService() {
        return $this->belongsTo('App\Users', 'id', 'service_id');
    }
    public function ServiceCategory() {
        return $this->belongsTo('App\ServiceCategory', 'cat_id', 'id');
    }
    
    public function coupons()
    {
        return $this->belongsToMany('App\Coupon');
    }
}
