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

    public function __construct($resource, $token = "") {
        parent::__construct($resource);
        $this->token = $token;
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
            'gender' => $this->gender ? $this->gender : '',
            'token' => $this->token,
            'refferal_code' => $this->refferal_code,
            'is_notification' => $this->is_notification
        ];

       
    }

}
