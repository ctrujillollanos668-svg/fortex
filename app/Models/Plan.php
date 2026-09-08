<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'daily_percentage',
        'duration_days',
        'max_return',
        'badge',
        'stock',
        'status',
        'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'daily_percentage' => 'decimal:2',
            'max_return' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public function hasStockLimit(): bool
    {
        return isset($this->attributes['stock']) && $this->attributes['stock'] !== null;
    }

    public function isSoldOut(): bool
    {
        return $this->hasStockLimit() && (int) $this->attributes['stock'] <= 0;
    }

    public function userPlans(): HasMany
    {
        return $this->hasMany(UserPlan::class);
    }
}
