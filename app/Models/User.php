<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'balance',
        'referral_code',
        'referred_by',
        'status',
        'last_spin_at',
        'roulette_spins',
        'claimed_red_packet',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'roulette_spins' => 1,
        'balance' => 0.00,
        'role' => 'cliente',
        'status' => 'active',
        'claimed_red_packet' => false,
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_spin_at' => 'datetime',
        'roulette_spins' => 'integer',
        'claimed_red_packet' => 'boolean',
        'password' => 'hashed',
        'balance' => 'decimal:2',
    ];

    public function canSpin(): bool
    {
        // Si nunca ha girado en la ruleta, siempre tiene al menos 1 tiro disponible
        if ($this->last_spin_at === null && ($this->roulette_spins === null || $this->roulette_spins <= 0)) {
            return true;
        }
        return (int) ($this->roulette_spins ?? 0) > 0;
    }

    public function secondsUntilNextSpin(): int
    {
        if (!$this->last_spin_at) {
            return 0;
        }

        $nextSpinTime = $this->last_spin_at->copy()->addHours(24);
        $diff = now()->diffInSeconds($nextSpinTime, false);

        return max(0, $diff);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'cliente';
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function userPlans(): HasMany
    {
        return $this->hasMany(UserPlan::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function commissionsReceived(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'sponsor_id');
    }

    /**
     * Total dinero recargado y aprobado en el sistema.
     */
    public function totalDeposited(): float
    {
        return (float) $this->deposits()->where('status', 'approved')->sum('amount');
    }

    /**
     * Total dinero de recarga que ha sido consumido para comprar planes VIP.
     */
    public function totalDepositSpentOnPlans(): float
    {
        return (float) abs($this->transactions()
            ->whereIn('type', ['plan_purchase', 'plan_purchase_deposit'])
            ->sum('amount'));
    }

    /**
     * Saldo de recargas disponible exclusivamente para comprar planes VIP.
     */
    public function rechargeBalance(): float
    {
        $deposited = $this->totalDeposited();
        $spent = $this->totalDepositSpentOnPlans();
        $remaining = max(0, round($deposited - $spent, 2));

        return min((float) $this->balance, $remaining);
    }

    /**
     * Saldo de ganancias acumuladas (rendimientos diarios, comisiones de referidos,
     * premios de ruleta y bonos).
     * Este saldo se puede retirar o re-invertir en nuevos planes VIP.
     */
    public function earningsBalance(): float
    {
        $currentBalance = (float) $this->balance;
        $recharge = $this->rechargeBalance();

        return max(0, round($currentBalance - $recharge, 2));
    }

    /**
     * Saldo de recarga que aún no ha sido invertido en planes VIP.
     */
    public function uninvestedDeposit(): float
    {
        return $this->rechargeBalance();
    }

    /**
     * Saldo retirable: Únicamente las ganancias obtenidas.
     */
    public function withdrawableBalance(): float
    {
        return $this->earningsBalance();
    }
}
