<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class vendorEducation extends Model
{
    
    protected $table = 'vender_education';
     
    protected $fillable = ['user_id','degree', 'batch', 'edu_desc'];
    
    protected $dates = ['updated_at','created_at','deleted_at'];
    
    public function users()
    {
        return $this->belongsTo('App\Users', 'id', 'vendor_id');
    }
        
        
        
    
    
}
