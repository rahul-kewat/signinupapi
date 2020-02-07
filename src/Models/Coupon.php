<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Coupon"))
 */
class Coupon extends Model
{

    /**
     * @var string
     * @SWG\Property(
     *   property="name",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="code",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="type",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="discount",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="minAmount",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="maxDiscountAmount",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="startDateTime",
     *   type="string" 
     * )
     * * @SWG\Property(
     *   property="endDateTime",
     *   type="string" 
     * )
     * * @SWG\Property(
     *   property="maxTotalUse",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="maxUseCustomer",
     *   type="integer" 
     * )
     * @SWG\Property(
     *   property="status",
     *   type="integer" 
     * )
     */

    protected $table = 'coupons';
    protected $fillable = ['name', 'code', 'type', 'discount', 'minAmount', 'maxDiscountAmount','startDateTime', 'endDateTime', 'maxTotalUse', 'maxUseCustomer', 'status'];


    /**
     * Get the startDateTime.
     *
     * @return string
     */
    public function getStartDateTimeAttribute($value)
    {
        return $value !== null ? $value : ' ';
    }

    /**
     * Get the endDateTime.
     *
     * @return string
     */
    public function getEndDateTimeAttribute($value)
    {
        return $value !== null ? $value : ' ';
    }
}
