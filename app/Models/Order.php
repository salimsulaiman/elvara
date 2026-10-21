<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_reference', 'user_id', 'subtotal', 'discount_amount', 
        'shipping_cost', 'grand_total', 'status_payment', 'status_delivery', 
        'shipping_courier', 'shipping_service', 'tracking_number', 
        'shipping_address', 'rajaongkir_destination_id', 'xendit_invoice_url'
    ];

    protected $casts = [
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'shipping_cost' => 'float',
        'grand_total' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_reference)) {
                $order->order_reference = 'ELV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'voucher_user')->withTimestamps();
    }
}
