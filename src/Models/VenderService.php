<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class VenderService extends Model
{
    //use SoftDeletes;
    protected $table = 'vender_services';
    protected $fillable = ['vender_id', 'price', 'cat_id','price','experience'];
    
    /**
     * Function to get user details here
     */
    public function users()
    {
        return $this->belongsTo('App\User','vender_id','id');
    }
    
    /**
     * Function to get venders reviews here
     */
    public function reviews()
    {
        return $this->hasMany('App\Review','vender_id','vender_id');
    }

    /**
     * Function to get vender service category here
     */
    public function servicecategory()
    {
        return $this->hasOne('App\ServiceCategory','id','cat_id');
    }

    /**
     * Function to get vender address here
     */
    public function venderaddress()
    {
        return $this->hasOne('App\UserAddresses','user_id','vender_id');
    }

    /**
     * Function to get vender slot here
     */
    public function venderSlot()
    {
        return $this->hasMany('App\venderSlot','vender_id','vender_id');
    }
}
