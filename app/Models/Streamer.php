<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Unified whitelist of livestream channels we want to surface on /streams.
 *
 * Replaces the old SoopStreamer model. Holds entries for both SOOP and
 * Twitch — uniqueness is scoped per platform (see the migration), so an
 * "afstar1" on SOOP and a (hypothetical) "afstar1" on Twitch coexist
 * without collision.
 */
class Streamer extends Model
{
    public const PLATFORM_SOOP   = 'soop';
    public const PLATFORM_TWITCH = 'twitch';

    public const PLATFORMS = [
        self::PLATFORM_SOOP,
        self::PLATFORM_TWITCH,
    ];

    protected $fillable = [
        'platform',
        'user_id',
        'label',
        'race',
    ];

    // Allowed race values (nullable in DB — casters/teams/official channels have no race).
    public const RACES = ['zerg', 'protoss', 'terran', 'random'];

    // ── Scopes ───────────────────────────────────────

    /**
     * Filter to a specific platform. Use the constants, not raw strings:
     *   Streamer::query()->platform(Streamer::PLATFORM_SOOP)->get();
     */
    public function scopePlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Shorthand: Streamer::soop()->get()
     */
    public function scopeSoop(Builder $query): Builder
    {
        return $query->where('platform', self::PLATFORM_SOOP);
    }

    /**
     * Shorthand: Streamer::twitch()->get()
     */
    public function scopeTwitch(Builder $query): Builder
    {
        return $query->where('platform', self::PLATFORM_TWITCH);
    }
}