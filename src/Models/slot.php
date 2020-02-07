<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class slot extends Model
{
    protected $table = 'slots';
    protected $fillable = ['day', 'slot_from', 'slot_to'];
    
    public function venderSlot()
    {
        return $this->hasMany('App\venderSlot', 'slot_id', 'id');
    }
    
    public function vender()
    {
        return $this->belongsToMany('App\User', 'vender_slots', 'slot_id', 'vender_id');
    }
}
