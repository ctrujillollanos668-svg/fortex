<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reward_amount',
        'max_uses',
        'used_count',
        'status',
        'description',
        'expires_at',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'status' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function redemptions()
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function isAvailable(): bool
    {
        if (!$this->status) {
            return false;
        }
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }
}
