<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|min:3',
            'sku' => 'sometimes|nullable|unique:products,sku,' . optional($this->product)->id,
            'description' => 'sometimes|nullable|max:1000',
            'product_variant' => 'required|array',
            'product_variant.*.option' => 'required|integer|exists:variants,id',
            'product_variant.*.tags' => 'sometimes|nullable|array',
            'product_variant_prices' => 'sometimes|nullable|array',
        ];
    }
}
