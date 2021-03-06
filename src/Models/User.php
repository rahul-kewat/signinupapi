<?php

namespace Devrahul\Signinupapi\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ultraware\Roles\Traits\HasRoleAndPermission;
use Ultraware\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;
use App\Notifications\ResetPasswordUser as ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="User"))
 */

class User extends Authenticatable implements HasRoleAndPermissionContract{

    
    /**
     * @var string
     * @SWG\Property(
     *   property="firstname",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="lastname",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="email",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="password",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="password_confirmation",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="status",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="phone_country_code",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="phone_number",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="fb_id",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="google_id",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="image",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="social_image",
     *   type="string" 
     * )
     * @SWG\Property(
     *   property="is_notification",
     *   type="string" 
     * )
     */

    use HasApiTokens, Notifiable , HasRoleAndPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    const inActive = '0';
    const active = '1';
    const pending = '2';
    const rejected = '3';
    
    protected $fillable = ['firstname','lastname', 'email', 'password','status','phone_number','phone_country_code','fb_id','google_id','image','social_image','is_notification','gender','referral_code','date_of_birth','bio','password_otp','no_of_attempts','is_verified'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Set the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Update Profile image path before send
     *
     * @param  string  $value
     * @return void
     */
    public function getImageAttribute($value)
    {
        return $value ? url("images/avatars/".$value) : '';
    }

     /**
     * Get the user's firstname.
     *
     * @param  string  $value
     * @return void
     */
    public function getFirstnameAttribute($value)
    {
        return $value;
    }

    /**
     * Get the user's role_id.
     *
     * @param  string  $value
     * @return void
     */
    public function getRoleIdAttribute($value)
    {
        return $value;
    }

        
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    
    public function scopeActive($query) {
        return $query->whereStatus('1');
    }
    
    public function scopeOnline($query) {
        return $query->whereOnline('1');
    }
    
    public function transaction() {
        return $this->hasMany('App\Transaction');
    }
  

    // public function userAddress(){
    //     return $this->hasOne('App\UserAddresses', 'user_id', 'id');
    // }

    // public function userManyAddress(){
    //     return $this->hasMany('App\UserAddresses', 'user_id', 'id');
    // }


    public function userPhoneOtp()
    {
        return $this->hasOne('App\phoneOtp', 'phone_no', 'phone_number');
    }

    public function userRole()
    {
        return $this->hasOne('App\RoleUser', 'user_id', 'id');
    }

  
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
    
    
    public function hasNotifications()
    {
        return $this->hasMany('App\Notification');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function role() {
        return $this->belongsToMany('App\Role')->withTimestamps();
    }

    public function userIsVehicle(){
        return $this->hasMany('App\Models\Vehicle','user_id','id');
    }


    public function homeAddress(){
        return $this->hasOne('App\UserAddresses', 'user_id', 'id')->where('address_type',1);
    }
    public function workAddress(){
        return $this->hasOne('App\UserAddresses', 'user_id', 'id')->where('address_type',2);
    }
    public function otherAddress(){
        return $this->hasOne('App\UserAddresses', 'user_id', 'id')->where('address_type',3);
    }
    
}
