<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    
    protected $fillable =[
        'name',
    ];

    public function users(){
        return $this->hadMany('Devrahul\Signinupapi\Models\User');
    }

}
