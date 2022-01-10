<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'price',
        'stock',
        'product_id',
        'product_variant_one',
        'product_variant_two',
        'product_variant_three'
        ];
    public function productVariantOne(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one', 'id');
    }

    public function productVariantTwo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two', 'id');
    }

    public function productVariantThree(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three', 'id');
    }

    public function getTitleAttribute(){
        $title = '';
        if ($this->productVariantOne){
            $title .= $this->productVariantOne->variant . '/';
        }
        if ($this->productVariantTwo){
            $title .= $this->productVariantTwo->variant . '/';
        }
        if ($this->productVariantThree){
            $title .= $this->productVariantThree->variant . '/';
        }

        return $title;
    }

}
