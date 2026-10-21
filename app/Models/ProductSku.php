<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductSku extends Model
{
    use HasFactory;

    protected $fillable = ['product_variant_id', 'sku', 'size', 'price', 'stock'];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
    ];

    protected $appends = ['active_discount', 'discounted_price'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product()
    {
        return $this->hasOneThrough(Product::class, ProductVariant::class, 'id', 'id', 'product_variant_id', 'product_id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'product_sku_discount')->withTimestamps();
    }

    /**
     * Get the active discount (if any) currently valid.
     */
    public function getActiveDiscountAttribute()
    {
        return $this->discounts()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->first();
    }

    /**
     * Get calculated discounted price.
     */
    public function getDiscountedPriceAttribute(): float
    {
        $discount = $this->active_discount;
        if (!$discount) {
            return $this->price;
        }

        if ($discount->type === 'percentage') {
            return (float) max(0, $this->price - ($this->price * ($discount->value / 100)));
        } else {
            return (float) max(0, $this->price - $discount->value);
        }
    }
}
