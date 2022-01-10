<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;

class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $file_path = public_path('storage/' . $this->file_path);
        if (!file_exists($file_path)) {
            return [];
        }
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'title' => File::basename($file_path),
            'base' => 'data:image/png;base64, ' . base64_encode(file_get_contents($file_path)),
            'file_url' => asset('storage/' . $this->file_path),
            'size' => File::size($file_path),
            'type' => File::mimeType($file_path),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
