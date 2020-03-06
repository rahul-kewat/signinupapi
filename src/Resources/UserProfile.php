<?php

namespace Devrahul\Signinupapi\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfile extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function __construct($resource,$is_vehicle_added,$is_bank_detail_added,$sug_price_value) {
        parent::__construct($resource);
        $this->sug_price_value = $sug_price_value;
        $this->is_vehicle_added = $is_vehicle_added;
        $this->is_bank_detail_added = $is_bank_detail_added;
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
            'refferal_code'=> $this->refferal_code ? $this->refferal_code : '',
            'sug_price_value' => $this->sug_price_value ? $this->sug_price_value : '0.00',
            'is_notification' => $this->is_notification != null ? $this->is_notification:'',
            'image' => $this->image ? $this->image : '',
            'bio' => $this->bio ? $this->bio : '',
            'is_vehicle_added' => $this->is_vehicle_added,
            'is_bank_detail_added' => $this->is_bank_detail_added ,
        ];
    }
}
