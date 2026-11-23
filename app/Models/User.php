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
     * Total de ganancias generadas por reclamos diarios de Planes VIP.
     */
    public function totalPlanEarnings(): float
    {
        return (float) $this->transactions()
            ->where('type', 'daily_claim')
            ->sum('amount');
    }

    /**
     * Total de comisiones ganadas por referidos (Nivel 1 y Nivel 2).
     */
    public function totalReferralEarnings(): float
    {
        return (float) $this->transactions()
            ->where('type', 'referral_commission')
            ->sum('amount');
    }

    /**
     * Total histórico de ganancias netas retirables ganadas por el usuario.
     * (Planes VIP + Comisiones de Red).
     * Los premios de la Ruleta VIP, sobres rojos y recargas NO cuentan aquí
     * porque son saldo exclusivo para comprar e invertir en planes.
     */
    public function totalRealEarnings(): float
    {
        return round($this->totalPlanEarnings() + $this->totalReferralEarnings(), 2);
    }

    /**
     * Total de ganancias que ya han sido retiradas o solicitadas para retiro.
     */
    public function totalEarningsWithdrawn(): float
    {
        return (float) abs($this->withdrawals()
            ->whereIn('status', ['approved', 'pending'])
            ->sum('amount'));
    }

    /**
     * Total de ganancias que el usuario decidió re-invertir en la compra de nuevos planes.
     */
    public function totalEarningsReinvested(): float
    {
        return (float) abs($this->transactions()
            ->where('type', 'plan_purchase_earnings')
            ->sum('amount'));
    }

    /**
     * Saldo Retirable Oficial:
     * Únicamente las ganancias generadas por Planes VIP y Comisiones de Referidos,
     * descontando lo que ya se retiró o re-invirtió.
     * La Ruleta VIP, bonos y recargas quedan 100% blindados exclusivamente para comprar planes.
     */
    public function withdrawableBalance(): float
    {
        $realEarnings = $this->totalRealEarnings();
        $withdrawn = $this->totalEarningsWithdrawn();
        $reinvested = $this->totalEarningsReinvested();

        $remainingEarnings = max(0, round($realEarnings - $withdrawn - $reinvested, 2));

        return min((float) $this->balance, $remainingEarnings);
    }

    /**
     * Saldo no retirable (saldo de recargas + premios de ruleta + bonos que deben ser invertidos en planes).
     */
    public function uninvestedDeposit(): float
    {
        $currentBalance = (float) $this->balance;
        $withdrawable = $this->withdrawableBalance();

        return max(0, round($currentBalance - $withdrawable, 2));
    }

    /**
     * Saldo de recargas y bonos disponibles para comprar planes VIP.
     */
    public function rechargeBalance(): float
    {
        return $this->uninvestedDeposit();
    }

    /**
     * Saldo de ganancias acumuladas retirable.
     */
    public function earningsBalance(): float
    {
        return $this->withdrawableBalance();
    }
}
