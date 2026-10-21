<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'weight', 'is_active'];

    protected $appends = ['price_range', 'cover_image', 'has_discount', 'discounted_sku_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProductGallery::class)->orderBy('sort_order');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get original and discounted price range.
     */
    public function getPriceRangeAttribute(): array
    {
        // Fetch all SKUs related to this product's variants
        $skus = ProductSku::whereIn(
            'product_variant_id', 
            $this->variants()->pluck('id')
        )->get();

        if ($skus->isEmpty()) {
            return [
                'min' => 0, 'max' => 0,
                'min_original' => 0, 'max_original' => 0,
                'has_discount' => false
            ];
        }

        $prices = $skus->pluck('price');
        $discountedPrices = $skus->map(fn($sku) => $sku->discounted_price);

        $hasDiscount = $skus->contains(fn($sku) => $sku->active_discount !== null);

        return [
            'min' => (float) $discountedPrices->min(),
            'max' => (float) $discountedPrices->max(),
            'min_original' => (float) $prices->min(),
            'max_original' => (float) $prices->max(),
            'has_discount' => $hasDiscount
        ];
    }

    /**
     * Check if product has any active discount on its SKUs.
     */
    public function getHasDiscountAttribute(): bool
    {
        return $this->price_range['has_discount'] ?? false;
    }

    /**
     * Get the first SKU ID that has an active discount (to link directly to it).
     */
    public function getDiscountedSkuIdAttribute(): ?int
    {
        foreach ($this->variants as $variant) {
            foreach ($variant->skus as $sku) {
                if ($sku->active_discount !== null) {
                    return $sku->id;
                }
            }
        }
        return null;
    }

    /**
     * Get first available cover image (variant image or gallery image).
     */
    public function getCoverImageAttribute(): ?string
    {
        $variantImage = $this->variants()->whereNotNull('variant_image')->first();
        if ($variantImage) {
            return $variantImage->variant_image;
        }

        $galleryImage = $this->galleries()->first();
        if ($galleryImage) {
            return $galleryImage->image_path;
        }

        return null;
    }
}
