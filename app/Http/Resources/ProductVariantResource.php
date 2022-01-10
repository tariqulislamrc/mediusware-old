<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    private $variant_id;

    public function __construct($resource, $variant_id)
    {
        // Ensure we call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->variant_id = $variant_id; // $apple param passed
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'option' => $this->variant_id,
            'tags' => $this->pluck('variant')->toArray(),
        ];
    }
}
