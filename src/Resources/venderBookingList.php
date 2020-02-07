<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\Address;

class venderBookingList extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
         
        return [
            
            'userId' => $this->user_id,
            'userName' => $this->full_name ? $this->full_name : '',
            'userProfilePic' => ($this->user->image) ? url('images/avatars/' . $this->user->image) : '',
            'serviceName' => $this->service_name,
            'serviceId' => $this->service_id,
            'bookingDate' => $this->booking_date,
            'bookingSlot' => $this->slot_start_from.'-'.$this->slot_start_end,
            'bookingId' => $this->id,
            'bookingPrice' => $this->total_price,
            'paymentPending' => $this->user->pending_payment ? $this->user->pending_payment : 0,
            //'action' => $this->action,
            
        ];
    }
}