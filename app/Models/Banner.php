<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image_path', 'link_url', 'order_position', 'is_active'];

    protected $casts = [
        'order_position' => 'integer',
        'is_active' => 'boolean',
    ];
}
