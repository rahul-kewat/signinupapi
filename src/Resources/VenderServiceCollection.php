<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VenderServiceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $collects = 'App\Http\Resources\VenderService';
    public function toArray($request)
    {
        $this->collection->transform(function (VenderService $data) {
            return (new VenderService($data));
        });

        return parent::toArray($request);
    }
}
