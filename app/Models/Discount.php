<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'value', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = [
        'value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function skus()
    {
        return $this->belongsToMany(ProductSku::class, 'product_sku_discount')->withTimestamps();
    }
}
