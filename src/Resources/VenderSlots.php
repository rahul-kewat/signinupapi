<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\VenderSlotsCollection;
use App\Http\Resources\VenderSlots;

class VenderSlots extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //$venderServiceCollection = new ServiceCollection($this->service);
        return [
            'id' => $this->id,
            'venderId' => $this->vender->firstname.' '.$this->vender->lastname,
            'slotFrom' => $this->slot->slot_from,
            'slotTo' => $this->slot->slot_to,
            'createdAt' => $this->created_at->format('y-m-d H:i:s'),
            'updatedAt' => $this->updated_at->format('y-m-d H:i:s')
        ];
    }
}
