<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'enquiries';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['fullname', 'email', 'subject', 'message', 'status'];

    public function scopePending($query) {
        return $query->whereStatus('pending');
    }
    
    public function scopeAnswered($query) {
        return $query->whereStatus('answered');
    }
}
