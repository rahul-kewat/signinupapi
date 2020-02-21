<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class phoneOtp extends Model
{
    protected $fillable = ['phone_no', 'otp','phone_country_code','no_of_attempts','is_verified'];
}
