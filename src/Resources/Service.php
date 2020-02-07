<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Service extends Resource
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
            'title' => $this->title,
            'description' => $this->description,
            'priceType' => (int) $this->price_type,
            'price' => (float) $this->price,
            'image' => $this->image ? url('/public/images').'/'.$this->image : '',
            'status' => intval($this->status),
        ];
    }
}
