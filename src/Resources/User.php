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
    private $referral_code_text;
    protected $address=array();
   

    public function __construct($resource,$token,$is_vehicle_added,$is_bank_detail_added,$sug_price_value,$referral_code_text) {
        parent::__construct($resource);
        $this->token = $token;
        $this->sug_price_value = $sug_price_value;
        $this->is_vehicle_added = $is_vehicle_added;
        $this->is_bank_detail_added = $is_bank_detail_added;
        $this->referral_code_text = $referral_code_text;
        $this->address = UserAddresses::where('user_id',Auth::user()->id)->get();
       
    }


    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname ? $this->firstname : '',
            'lastname' => $this->lastname ? $this->lastname : '',
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth : '',
            'email' => $this->email ? $this->email : '',
            'phone_number' => $this->phone_number,
            'phone_country_code' => $this->phone_country_code ? $this->phone_country_code : '',
            'gender' => $this->gender==0 ? '0' : '1' ,
            'referral_code'=> $this->referral_code ? $this->referral_code : '',
            'sug_price_value' => $this->sug_price_value ? $this->sug_price_value : '0.00',
            'is_notification' => $this->is_notification != null ? $this->is_notification:'',
            'image' => $this->image ? $this->image : '',
            'bio' => $this->bio ? $this->bio : '',
            'token' => $this->token ? $this->token : '',
            'is_vehicle_added' => $this->is_vehicle_added,
            'is_bank_detail_added' => $this->is_bank_detail_added ,
            'referral_code_text' => $this->referral_code_text != null ? $this->referral_code_text : '',
            'address' => $this->address != null ? $this->address : '',
        ];
    }
}
