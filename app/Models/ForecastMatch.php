<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForecastMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'season_id',
        'event_id',
        'player_a_id',
        'player_b_id',
        'player_a_name',
        'player_b_name',
        'player_a_race',
        'player_b_race',
        'player_a_country',
        'player_b_country',
        'odds_a',
        'odds_b',
        'multiplier',
        'match_type',
        'scheduled_at',
        'locked_at',
        'winner_id',
        'winner_side',
        'settled_at',
        'settled_by',
    ];

    protected $casts = [
        'odds_a'        => 'decimal:2',
        'odds_b'        => 'decimal:2',
        'multiplier'    => 'decimal:2',
        'scheduled_at'  => 'datetime',
        'locked_at'     => 'datetime',
        'settled_at'    => 'datetime',
    ];

    // How many minutes before scheduled_at betting locks by default
    const DEFAULT_LOCK_MINUTES = 60;

    const MATCH_TYPES = ['foreigner', 'korean', 'clan', 'national'];

    // ── Relationships ─────────────────────────────────

    public function season(): BelongsTo
    {
        return $this->belongsTo(ForecastSeason::class, 'season_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function playerA(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_a_id');
    }

    public function playerB(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_b_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(ForecastPrediction::class, 'match_id');
    }

    // ── Scopes ────────────────────────────────────────

    public function scopeOpen($query)
    {
        return $query->whereNull('winner_id')
            ->whereNull('winner_side')          // ← add this
            ->where('locked_at', '>', now());
    }

    public function scopeLocked($query)
    {
        return $query->whereNull('winner_id')
            ->whereNull('winner_side')          // ← add this
            ->where('locked_at', '<=', now());
    }

    public function scopeSettled($query)
    {
        // A match is settled when either a winner player (foreigner)
        // or a winner side (korean/clan/national) has been recorded.
        return $query->where(function ($q) {
            $q->whereNotNull('winner_id')
              ->orWhereNotNull('winner_side');
        });
    }

    // ── Helpers ───────────────────────────────────────

    public function isOpen(): bool
    {
        return is_null($this->winner_id) && $this->locked_at->isFuture();
    }

    public function isLocked(): bool
    {
        return is_null($this->winner_id) && $this->locked_at->isPast();
    }

    public function isSettled(): bool
    {
        return ! is_null($this->winner_id) || ! is_null($this->winner_side);
    }

    public function isForeigner(): bool
    {
        return $this->match_type === 'foreigner';
    }

    // Calculate ELO-based odds for a foreigner match
    public static function calculateOdds(int $eloA, int $eloB): array
    {
        $probA = 1 / (1 + pow(10, ($eloB - $eloA) / 800));
        $probB = 1 - $probA;

        return [
            'odds_a' => round(1 / $probA, 2),
            'odds_b' => round(1 / $probB, 2),
        ];
    }
}