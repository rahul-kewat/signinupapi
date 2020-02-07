<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Faq"))
 */

class Faq extends Model
{

    /**
     * @var string
     * @SWG\Property(
     *   property="question",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="answer",
     *   type="string" 
     * )
     */
    protected $table = 'faq';

    protected $fillable = ['question', 'answer','user_id','language'];

}
