<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\ServiceCollection;
use App\Http\Resources\Service;

class VenderService extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        print_r($this->id); exit;
        return [
                       
        ];
    }
}
