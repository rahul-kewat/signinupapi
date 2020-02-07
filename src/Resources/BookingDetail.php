<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class BookingDetail extends Resource
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
            'id' => $this->id,
            'bookingId' => $this->booking_id,
            'code' => $this->code ? $this->code : '',
            'label' => $this->label ? $this->label : '',
            'price' => (float) $this->price ? $this->price : '',
            'amount' => (float) $this->amount,
            'startTime' => $this->start_time ? $this->start_time : '',
            'endTime' => $this->end_time ? $this->end_time : '',
            'createdAt' => $this->created_at ? $this->created_at->format('y-m-d H:i:s') : '',
            'updatedAt' => $this->updated_at ? $this->updated_at->format('y-m-d H:i:s') : ''
        ];
    }
}
