<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Card"))
 */

class Card extends Model
{
   /**
     * @var string
     * @SWG\Property(
     *   property="gateway_id",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="card_number",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="type",
     *   type="string" 
     * )
     */

      /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'gateway_id', 'card_number', 'type'];
}
