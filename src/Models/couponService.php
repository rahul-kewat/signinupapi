<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class couponService extends Model
{
    protected $table = 'coupon_service';
    protected $fillable = ['coupon_id', 'service_id'];
}
