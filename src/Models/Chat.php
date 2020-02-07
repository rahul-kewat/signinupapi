<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_chat';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'vender_id', 'message_content', 'message_read', 'type'];

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function vender() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }

}
