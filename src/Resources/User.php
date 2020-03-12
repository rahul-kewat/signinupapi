<?php

namespace Devrahul\Signinupapi\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\UserAddresses;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\VenderServiceCollection;
use Illuminate\Support\Facades\Auth;

class User extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private $token;
    private $sug_price_value;
    private $is_vehicle_added;
    private $is_bank_detail_added;

    public function __construct($resource, $token = "",$is_vehicle_added,$is_bank_detail_added,$sug_price_value) {
        parent::__construct($resource);
        $this->token = $token;
        $this->sug_price_value = $sug_price_value;
        $this->is_vehicle_added = $is_vehicle_added;
        $this->is_bank_detail_added = $is_bank_detail_added;
    }

    public function toArray($request) {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname ? $this->firstname : '',
            'lastname' => $this->lastname ? $this->lastname : '',
            'email' => $this->email ? $this->email : '',
            'phone_number' => $this->phone_number ? $this->phone_number : '',
            'image' => $this->image ? $this->image : '',
            'phone_country_code' => $this->phone_country_code ? $this->phone_country_code : '',
            'gender' => $this->gender==0 ? '0' : '1' ,
            'token' => $this->token,
            'bio' => $this->bio ? $this->bio : '',
            'sug_price_value' => $this->sug_price_value ? $this->sug_price_value : '0.00',
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth : '',
            'referral_code' => $this->referral_code != null ? $this->referral_code : '' ,
            'is_notification' => $this->is_notification != null ? $this->is_notification : '',
            'is_vehicle_added' => $this->is_vehicle_added != null ? $this->is_vehicle_added : '0',
            'is_bank_detail_added' => $this->is_bank_detail_added != null ? $this->is_bank_detail_added : '0',
        ];
        
    }

}
