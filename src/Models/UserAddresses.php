<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddresses extends Model
{
    protected $table = 'user_addresses';
    protected $fillable = ['user_id', 'place_id', 'name', 'phone', 'gender', 'latitude', 'longitude', 'city','state', 'house_no', 'landmark', 'country', 'pincode', 'full_address', 'address_type'];
    
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    /**
     * Get the user's Full Address.
     *
     * @param  string  $value
     * @return void
     */
    public function getFullAddressAttribute($value)
    {   
        return is_null($value) ? '': $value;
    }

    /**
     * Get the user's Full Address.
     *
     * @param  string  $value
     * @return void
     */
    public function getPincodeAttribute($value)
    {   
        return is_null($value) ? '': $value;
    }
}
