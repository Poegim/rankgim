<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastPrediction extends Model
{
    protected $fillable = [
        'user_id',
        'match_id', 
        'wallet_id',
        'pick_player_id',
        'pick_side',        // dodaj
        'stake',
        'odds_at_time',
        'bonus_multiplier',
        'potential_payout',
        'actual_payout',
        'result',
        'refunded_at',
    ];

    protected $casts = [
        'stake'            => 'decimal:2',
        'odds_at_time'     => 'decimal:2',
        'bonus_multiplier' => 'decimal:2',
        'potential_payout' => 'decimal:2',
        'actual_payout'    => 'decimal:2',
        'refunded_at'      => 'datetime',
    ];

    // Currency bonus multipliers per race
    const RACE_BONUS = [
        'khaydarin' => ['race' => 'Protoss', 'multiplier' => 1.25],
        'biomass'   => ['race' => 'Zerg',    'multiplier' => 1.25],
        'credits'   => ['race' => 'Terran',  'multiplier' => 1.25],
    ];

    const MINERALS_UNDERDOG_BONUS = 1.50;

    // ── Relationships ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(ForecastMatch::class, 'match_id');
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(ForecastWallet::class, 'wallet_id');
    }

    public function pickedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'pick_player_id');
    }

    // ── Scopes ────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('result', 'pending');
    }

    public function scopeSettled($query)
    {
        return $query->whereIn('result', ['won', 'lost']);
    }

    // ── Helpers ───────────────────────────────────────

    public function isPending(): bool
    {
        return $this->result === 'pending';
    }

    public function isWon(): bool
    {
        return $this->result === 'won';
    }

    // Calculate which bonus multiplier applies based on currency and match context
    public static function resolveBonusMultiplier(
        string $currency,
        string $pickedRace,
        float $pickedOdds,
        float $otherOdds,
    ): float {
        // Minerals: bonus if picking the underdog (higher odds = lower probability = underdog)
        if ($currency === 'minerals') {
            return $pickedOdds > $otherOdds ? self::MINERALS_UNDERDOG_BONUS : 1.00;
        }

        // Race-based currencies: bonus if picked player's race matches currency race
        if (isset(self::RACE_BONUS[$currency])) {
            $bonus = self::RACE_BONUS[$currency];

            // No bonus for Random or Unknown races
            if (in_array($pickedRace, ['Random', 'Unknown'])) {
                return 1.00;
            }

            return $pickedRace === $bonus['race'] ? $bonus['multiplier'] : 1.00;
        }

        return 1.00;
    }
}