<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'title' => $this->title,
            'sku' => $this->sku,
            'description' => $this->description,
            'variant_prices' => ProductVariantPriceResource::collection($this->whenLoaded('variantPrices')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'product_variants' => ProductVariantResource::collection($this->whenLoaded('productVariants', function () {
                return $this->productVariants->groupBy('variant_id');
            }, [])),
        ];
    }
}
