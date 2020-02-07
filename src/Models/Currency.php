<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
   
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'currencies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'code', 'language_code', 'country_code', 'currency_symbol','status'];
    
    public function scopeActive($query) {
        return $query->whereStatus('1');
    }
    
    public function paymentSetting() {
        return $this->hasOne('App\PaymentSetting');
    }
    public function transaction() {
        return $this->hasOne('App\transaction');
    }
}
