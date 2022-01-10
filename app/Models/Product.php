<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!request('sku')){
                $sku = Str::slug($model->title);
                $count = static::whereRaw("sku RLIKE '^{$sku}(-[0-9]+)?$'")->count();
                $model->sku = $count ? "{$sku}-{$count}" : $sku;
            }
        });

        static::updating(function ($model) {
            if (!request('sku')){
                $sku = Str::slug($model->title);
                $count = static::whereRaw("sku RLIKE '^{$sku}(-[0-9]+)?$'")->count();
                $model->sku = $count ? "{$sku}-{$count}" : $sku;
            }
        });
    }

    protected $fillable = [
        'title', 'sku', 'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function productVariants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantPrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

    public function images(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

}
