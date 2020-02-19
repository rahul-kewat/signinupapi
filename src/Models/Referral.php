<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="User"))
 */

class Refferal extends Model{
    protected $table = 'refferal';
    protected $fillable = ['user_id', 'refferal_code', 'max_use', 'active'];
    
}
