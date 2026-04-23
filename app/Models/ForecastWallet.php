<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForecastWallet extends Model
{
    protected $fillable = [
        'user_id',
        'season_id',
        'currency',
        'balance',
        'resets_count',
    ];

    protected $casts = [
        'balance'      => 'decimal:2',
        'resets_count' => 'integer',
    ];

    // Starting balance for every season
    const STARTING_BALANCE = 50.00;

    // Reset is only allowed when balance is at or below this threshold
    const RESET_THRESHOLD = 10.00;

    // Available currencies with display info
    const CURRENCIES = [
        'minerals'  => [
            'label' => 'Mineral Points',
            'icon'  => '⬡',
            'bonus' => '+50% payout when betting on the underdog (lower ELO)',
        ],
        'khaydarin' => [
            'label' => 'Khaydarin Points',
            'icon'  => '🔷',
            'bonus' => '+25% payout when betting on a Protoss player',
        ],
        'biomass'   => [
            'label' => 'Biomass Points',
            'icon'  => '🟢',
            'bonus' => '+25% payout when betting on a Zerg player',
        ],
        'credits'   => [
            'label' => 'Credit Points',
            'icon'  => '💳',
            'bonus' => '+25% payout when betting on a Terran player',
        ],
    ];

    // ── Relationships ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(ForecastSeason::class, 'season_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(ForecastPrediction::class, 'wallet_id');
    }

    // ── Helpers ───────────────────────────────────────

    public function canReset(): bool
    {
        if ($this->balance > self::RESET_THRESHOLD) {
            return false;
        }

        // Cannot reset if there are pending bets — wait for them to settle
        return ! $this->predictions()
            ->where('result', 'pending')
            ->exists();
    }

    public function reset(): void
    {
        $this->balance = self::STARTING_BALANCE;
        $this->resets_count++;
        $this->save();
    }

    public function currencyLabel(): string
    {
        return self::CURRENCIES[$this->currency]['label'] ?? $this->currency;
    }

    public function currencyIcon(): string
    {
        return self::CURRENCIES[$this->currency]['icon'] ?? '';
    }

    // Calculate total profit from settled predictions only
    public function profit(): float
    {
        return (float) $this->predictions()
            ->whereIn('result', ['won', 'lost'])
            ->selectRaw('SUM(actual_payout) - SUM(stake) as profit')
            ->value('profit') ?? 0.00;
    }
}