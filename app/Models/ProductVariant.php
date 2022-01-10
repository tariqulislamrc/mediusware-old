<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['variant', 'product_id', 'variant_id'];

    public function variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }
}
