<?php


namespace Devrahul\Signinupapi\Traits;

use Lcobucci\JWT\Parser;
use Auth;
use Illuminate\Support\Facades\Request;
use Devrahul\Signinupapi\Models\DeviceDetails;
use Devrahul\Signinupapi\Models\User;
use FCM;

trait ApiUserTrait
{
    public function insertDeviceDetails($token, $userId = '')
    {
        $tokenId = (new Parser())->parse($token)->getHeader('jti');
        DeviceDetails::create([
            'access_token_id' => $tokenId,
            'device_token' => Request::header('device-token'),
            'device_id' => Request::header('device-id'),
            'build_version' => Request::header('build-version'),
            'platform' => Request::header('platform'),
            'build' => Request::header('build'),
            'user_id' => $userId,
        ]);
        return true;
    }

    public function userDetailsResponse($userId, $token = "")
    {
        $user = User::find($userId);
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['token'] = $token;
        $data['profile_image'] = $user->avatar;
        $data['is_verified'] = $user->is_activated;
        $data['phone_number'] = $user->phone_number;
        return $data;
    }
}
