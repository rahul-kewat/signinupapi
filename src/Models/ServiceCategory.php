<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $table = 'service_categories';
    protected $fillable = ['language','cat_name', 'image', 'parent_id', 'status'];
    
    public function Services()
    {
        return $this->hasMany('App\service', 'cat_id', 'id');
    }
    
    public function ServicesWithCon($ids)
    {
        return $this->hasMany('App\service', 'cat_id', 'id')->whereIn('id', $ids);
    }
}
