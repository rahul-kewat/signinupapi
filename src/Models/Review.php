<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $fillable = ['user_id', 'vender_id', 'booking_id', 'rating', 'is_like', 'review_submitted_by', 'review_submitted_to', 'review_type', 'feedback_message'];
    
    
    public function slot() {
        return $this->belongsTo('App\slot', 'slot_id', 'id');
    }
    public function booking() {
        return $this->belongsTo('App\slot', 'booking_id', 'id');
    }
    
    public function vender() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
