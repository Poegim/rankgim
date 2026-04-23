<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastSeasonSnapshot extends Model
{
    protected $fillable = [
        'season_id',
        'user_id',
        'currency',
        'final_profit',
        'final_balance',
        'total_predictions',
        'correct_predictions',
        'rank',
    ];

    protected $casts = [
        'final_profit'        => 'decimal:2',
        'final_balance'       => 'decimal:2',
        'total_predictions'   => 'integer',
        'correct_predictions' => 'integer',
        'rank'                => 'integer',
    ];

    // ── Relationships ─────────────────────────────────

    public function season(): BelongsTo
    {
        return $this->belongsTo(ForecastSeason::class, 'season_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────

    public function accuracy(): float
    {
        if ($this->total_predictions === 0) {
            return 0.0;
        }

        return round($this->correct_predictions / $this->total_predictions * 100, 1);
    }

    public function currencyLabel(): string
    {
        return ForecastWallet::CURRENCIES[$this->currency]['label'] ?? $this->currency;
    }

    public function currencyIcon(): string
    {
        return ForecastWallet::CURRENCIES[$this->currency]['icon'] ?? '';
    }
}