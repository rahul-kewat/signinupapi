<?php

namespace Devrahul\Signinupapi\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use App\User;

class DeviceDetails extends Model
{
    const androidPlatform = 1;
    const iosPlatform = 0;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'device_details';
    protected $fillable = ['access_token_id', 'device_token', 'device_id', 'build_version', 'platform', 'build','user_id'];

    public function scopeAndroidTokens($query)
    {
        return $query->where('platform', 1)->whereNotNull('device_token');
    }

    public function scopeIosToken($query)
    {
        return $query->where('platform', 0)->whereNotNull('device_token');
    }


    /**
     * Send push notification to device
     * @param {title,body,user_id,data(array)}
     * @return Array
     * @author Durga Parshad
     * @since 10-12-2019
     */
    public static function sendNotification($title,$body,$user_id,$data = null){
        
        $user = User::find($user_id);

        if($user->is_notification == 1){
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);

            $notificationBuilder = new PayloadNotificationBuilder($title);
            $notificationBuilder->setBody($body)
                                ->setSound('default');
            $dataBuilder = new PayloadDataBuilder();
            if($data !== null){
                $dataBuilder->addData($data);
            }
            

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();

            $token = Self::where('user_id',$user_id)
                        ->pluck('device_token')
                        ->toArray();
            return count($token) > 0 ? FCM::sendTo($token, $option, $notification, $data) : true;
            
        }

        return true;
        
    }
    
        public static function sendBulkNotification($title,$body,$user_id,$data = null){
            $user = User::whereIn('id',$user_id)->where('is_notification',1)->pluck('id')->toArray();
            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setTimeToLive(60*20);

            $notificationBuilder = new PayloadNotificationBuilder($title);
            $notificationBuilder->setBody($body)
                                ->setSound('default');
            $dataBuilder = new PayloadDataBuilder();
            if($data !== null){
                $dataBuilder->addData($data);
            }
            
            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $data = $dataBuilder->build();
            $token = Self::whereIn('user_id',$user)
                        ->pluck('device_token')
                        ->toArray();
            return count($token) > 0 ? FCM::sendTo($token, $option, $notification, $data) : true;
  
    }

}
