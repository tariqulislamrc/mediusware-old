<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantPriceResource extends JsonResource
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
            'price' => $this->price,
            'stock' => $this->stock
        ];
    }
}
