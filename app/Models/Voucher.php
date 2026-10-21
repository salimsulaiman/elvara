<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'reward_value', 'min_order_amount', 
        'global_limit', 'global_used', 'per_user_limit', 
        'start_date', 'end_date', 'is_active'
    ];

    protected $casts = [
        'reward_value' => 'float',
        'min_order_amount' => 'float',
        'global_limit' => 'integer',
        'global_used' => 'integer',
        'per_user_limit' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_user')->withPivot('order_id')->withTimestamps();
    }

    /**
     * Check if the voucher is valid for a given user and purchase subtotal.
     */
    public function isValidFor(?User $user, float $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ($subtotal < $this->min_order_amount) {
            return false;
        }

        if ($this->global_limit !== null && $this->global_used >= $this->global_limit) {
            return false;
        }

        if ($user) {
            $userUsedCount = $user->usedVouchers()->where('voucher_id', $this->id)->count();
            if ($userUsedCount >= $this->per_user_limit) {
                return false;
            }
        }

        return true;
    }
}
