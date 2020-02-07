<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $fillable = ['name', 'type', 'extension', 'user_id'];

    /**
     * Function to get uploaded user details
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
