<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Notification"))
 */

class Notification extends Model {
    /**
     * @var string
     * @SWG\Property(
     *   property="user_id",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="vender_id",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="type",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="title",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="message",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="is_read",
     *   type="integer" 
     * )
     */

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'vender_id', 'type', 'title','message','is_read'];


    public function vendor() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
}
