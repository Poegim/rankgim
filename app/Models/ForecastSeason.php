<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForecastSeason extends Model
{
    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(ForecastWallet::class, 'season_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ForecastMatch::class, 'season_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ForecastSeasonSnapshot::class, 'season_id');
    }

    // ── Scopes ────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ───────────────────────────────────────

    public static function current(): ?self
    {
        return static::active()->latest()->first();
    }
}