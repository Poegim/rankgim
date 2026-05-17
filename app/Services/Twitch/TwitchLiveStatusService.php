<?php

namespace App\Services\Twitch;

use App\Models\Streamer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Owns the cached snapshot of live Twitch StarCraft: Brood War streams.
 *
 * Mirrors SoopLiveStatusService in shape so LiveStreamsService can merge
 * results from both providers without caring which one they came from.
 *
 * Caching strategy (same as SOOP):
 *   - refresh() is called by the scheduler every 5 minutes; it writes to
 *     a forever cache key under `twitch:live:starcraft`.
 *   - Reads (whitelistedLiveStreams / otherLiveStreams) never trigger a
 *     network call — they just hand back whatever's in the cache.
 *   - isStale() lets the UI surface a "data may be outdated" hint.
 */
class TwitchLiveStatusService
{
    public const CACHE_KEY = 'twitch:live:starcraft';

    // Anything older than this is considered stale for UI purposes.
    private const STALE_THRESHOLD_SECONDS = 5 * 60;

    public function __construct(private readonly TwitchApiClient $client)
    {
    }

    /**
     * Twitch returns started_at as ISO-8601 UTC (e.g. "2026-05-17T15:32:00Z").
     * Convert to a Carbon instance so the UI can call diffForHumans() on it
     * — same shape as SOOP's parseDate() returns.
     */
    private function parseDate(?string $value): ?\Illuminate\Support\Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Force a fresh fetch from the Twitch API and overwrite the cache.
     * Called by the scheduler (RefreshTwitchStreams command).
     *
     * On failure: logs and leaves the existing cached snapshot in place,
     * so a transient Twitch outage doesn't wipe the UI.
     */
    public function refresh(): void
    {
        try {
            $gameId  = config('services.twitch.bw_game_id');
            $streams = $this->client->fetchLiveStreamsByGame($gameId);

            $normalized = collect($streams)
                ->map(fn (array $stream) => $this->normalize($stream))
                ->values()
                ->all();

            Cache::forever(self::CACHE_KEY, [
                'fetched_at' => CarbonImmutable::now()->toIso8601String(),
                'streams'    => $normalized,
            ]);
        } catch (Throwable $e) {
            Log::warning('Twitch refresh failed; keeping previous snapshot', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Streams from whitelisted Twitch accounts (joined against the streamers table).
     * Returns rows merged with their whitelist `label` and `race` so the UI
     * card can render them identically to SOOP entries.
     */
    public function whitelistedLiveStreams(): array
    {
        $live = $this->cachedStreams();
        if ($live->isEmpty()) {
            return [];
        }

        // Build a lookup of whitelisted Twitch logins → {label, race}.
        // Twitch logins are case-insensitive; we normalize to lowercase
        // on both sides to avoid mismatches.
        $whitelist = Streamer::twitch()
            ->get(['user_id', 'label', 'race'])
            ->keyBy(fn ($row) => mb_strtolower($row->user_id));

        if ($whitelist->isEmpty()) {
            return [];
        }

        return $live
            ->filter(fn (array $stream) => $whitelist->has(mb_strtolower($stream['user_id'])))
            ->map(function (array $stream) use ($whitelist) {
                $entry = $whitelist->get(mb_strtolower($stream['user_id']));
                return array_merge($stream, [
                    'label' => $entry->label,
                    'race'  => $entry->race,
                ]);
            })
            ->sortByDesc('viewers')
            ->values()
            ->all();
    }

    /**
     * Streams *not* on the whitelist — i.e. the long tail of random Twitch
     * BW streamers. UI renders these in a separate "Other live streams" section.
     */
    public function otherLiveStreams(): array
    {
        $live = $this->cachedStreams();
        if ($live->isEmpty()) {
            return [];
        }

        $whitelistedIds = Streamer::twitch()
            ->pluck('user_id')
            ->map(fn (string $id) => mb_strtolower($id))
            ->all();

        return $live
            ->reject(fn (array $stream) => in_array(mb_strtolower($stream['user_id']), $whitelistedIds, true))
            ->sortByDesc('viewers')
            ->values()
            ->all();
    }

    public function lastFetchedAt(): ?CarbonImmutable
    {
        $snapshot = Cache::get(self::CACHE_KEY);
        $iso      = $snapshot['fetched_at'] ?? null;

        return is_string($iso) ? CarbonImmutable::parse($iso) : null;
    }

    public function isStale(): bool
    {
        $fetchedAt = $this->lastFetchedAt();
        if ($fetchedAt === null) {
            return true; // No data yet → treat as stale.
        }

        return $fetchedAt->diffInSeconds(CarbonImmutable::now()) > self::STALE_THRESHOLD_SECONDS;
    }

    /**
     * Pull the normalized stream list from cache as a Collection.
     * Returns an empty collection if the cache hasn't been populated yet
     * (first run before scheduler kicks in).
     */
    private function cachedStreams(): Collection
    {
        $snapshot = Cache::get(self::CACHE_KEY);
        return collect($snapshot['streams'] ?? []);
    }

    /**
     * Convert a raw Helix /streams payload into the unified shape the UI
     * expects. Must match SoopLiveStatusService::normalize() field-for-field
     * (plus the `platform` discriminator).
     *
     * Helix /streams fields we care about:
     *   user_login      → channel slug, used for the play_url
     *   user_name       → display name (preserves capitalization / locale)
     *   title           → current broadcast title
     *   viewer_count    → integer
     *   thumbnail_url   → template with {width}/{height} placeholders
     *   started_at      → ISO-8601 stream start timestamp
     */
    private function normalize(array $stream): array
    {
        $login = (string) ($stream['user_login'] ?? '');

        return [
            'platform'    => 'twitch',
            // Use lowercase login as the canonical id (matches what we'll
            // store in the streamers table for Twitch entries).
            'user_id'     => $login,
            'user_nick'   => (string) ($stream['user_name'] ?? $login),
            'broad_title' => (string) ($stream['title'] ?? ''),
            'viewers'     => (int) ($stream['viewer_count'] ?? 0),
            'thumbnail'   => $this->buildThumbnail($stream['thumbnail_url'] ?? null),
            'started_at'  => $this->parseDate($stream['started_at'] ?? null),
            'play_url'    => $login !== '' ? "https://twitch.tv/{$login}" : null,
            // `label` and `race` get filled in by whitelistedLiveStreams() for
            // whitelisted entries; non-whitelisted entries leave them empty.
            'label'       => null,
            'race'        => null,
        ];
    }

    /**
     * Helix returns thumbnails as templates like:
     *   https://.../live_user_zzzeropl-{width}x{height}.jpg
     * We substitute a sensible default size for card display.
     */
    private function buildThumbnail(?string $template): ?string
    {
        if (! is_string($template) || $template === '') {
            return null;
        }

        return str_replace(['{width}', '{height}'], ['320', '180'], $template);
    }
}