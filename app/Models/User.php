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
     * Total dinero invertido en la compra y activación de Planes VIP.
     */
    public function totalSpentOnPlans(): float
    {
        $fromPlans = (float) $this->userPlans()->sum('invested_amount');
        $fromTx = (float) abs($this->transactions()
            ->whereIn('type', ['plan_purchase', 'plan_purchase_deposit', 'plan_purchase_earnings'])
            ->sum('amount'));

        return max($fromPlans, $fromTx);
    }

    /**
     * Compatibilidad hacia atrás.
     */
    public function totalDepositSpentOnPlans(): float
    {
        return $this->totalSpentOnPlans();
    }

    /**
     * Total de ganancias generadas por reclamos diarios de Planes VIP.
     * Verifica tanto la tabla de user_plans como el libro contable de transacciones.
     */
    public function totalPlanEarnings(): float
    {
        $fromTx = (float) $this->transactions()
            ->where('type', 'daily_claim')
            ->sum('amount');

        $fromPlans = (float) $this->userPlans()->sum('earned_so_far');

        return max($fromTx, $fromPlans);
    }

    /**
     * Total de comisiones ganadas por referidos (Nivel 1 y Nivel 2).
     */
    public function totalReferralEarnings(): float
    {
        $fromTx = (float) $this->transactions()
            ->where('type', 'referral_commission')
            ->sum('amount');

        $fromComm = (float) $this->commissionsReceived()->sum('amount');

        return max($fromTx, $fromComm);
    }

    /**
     * Total histórico de bonos no retirables recibidos (Ruleta VIP, sobres rojos, códigos promocionales).
     */
    public function totalBonusReceived(): float
    {
        return (float) $this->transactions()
            ->whereIn('type', ['roulette_reward', 'promo_code', 'welcome_bonus'])
            ->sum('amount');
    }

    /**
     * Saldo de recargas y bonos que aún NO han sido invertidos en Planes VIP.
     * Este saldo queda 100% blindado para comprar planes y NO se puede retirar.
     */
    public function uninvestedDeposit(): float
    {
        $totalLockedReceived = $this->totalDeposited() + $this->totalBonusReceived();
        $totalSpent = $this->totalSpentOnPlans();

        $remainingLocked = max(0, round($totalLockedReceived - $totalSpent, 2));

        return min((float) $this->balance, $remainingLocked);
    }

    /**
     * Saldo disponible exclusivamente para comprar planes VIP.
     */
    public function rechargeBalance(): float
    {
        return $this->uninvestedDeposit();
    }

    /**
     * Saldo Retirable Oficial:
     * Todo el saldo generado por reclamos diarios de Planes VIP y comisiones de referidos.
     * Las recargas y ruleta no invertidas quedan descontadas.
     */
    public function withdrawableBalance(): float
    {
        $currentBalance = (float) $this->balance;
        $locked = $this->uninvestedDeposit();

        $withdrawable = max(0, round($currentBalance - $locked, 2));

        return min($currentBalance, $withdrawable);
    }

    /**
     * Saldo de ganancias acumuladas retirable.
     */
    public function earningsBalance(): float
    {
        return $this->withdrawableBalance();
    }
}
