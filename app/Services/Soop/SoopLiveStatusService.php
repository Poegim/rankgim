<?php

namespace App\Services\Soop;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Owns the cache for SOOP live broadcasts and the filter pipeline
 * that decides which streams the UI should render.
 *
 * Refresh is driven by the scheduler — UI never triggers fetches.
 * Cache is forever (no TTL) and overwritten each refresh, so we
 * fall back to "stale data with timestamp" when SOOP is unreachable
 * instead of showing an empty page.
 */
class SoopLiveStatusService
{
    /**
     * Cache key holding the last successful API response.
     *
     * Schema: ['fetched_at' => ISO string, 'broadcasts' => array<int, array>]
     */
    protected const CACHE_KEY = 'soop:live:starcraft';

    /**
     * Streams older than this are considered stale by the UI badge,
     * even though we still render them.
     */
    public const STALE_AFTER_MINUTES = 5;

    public function __construct(protected SoopApiClient $client) {}

    /**
     * Fetch fresh data from SOOP and overwrite the cache.
     *
     * Called by the RefreshSoopStreams artisan command.
     * Returns the number of broadcasts fetched (for logging).
     */
    public function refresh(): int
    {
        $categoryNo = (string) config('services.soop.bw_category');
        $broadcasts = $this->client->fetchLiveBroadcastsByCategory($categoryNo);

        Cache::forever(self::CACHE_KEY, [
            'fetched_at' => now()->toIso8601String(),
            'broadcasts' => $broadcasts,
        ]);

        return count($broadcasts);
    }

    /**
     * Return only the streams whose user_id is registered in the
     * soop_streamers table, sorted by viewers DESC.
     *
     * Output shape (per row):
     *   [
     *     'user_id'      => string,
     *     'user_nick'    => string,
     *     'broad_no'     => string,
     *     'broad_title'  => string,
     *     'viewers'      => int,
     *     'thumbnail'    => string|null,
     *     'started_at'   => Carbon|null,
     *     'play_url'     => string,
     *     'label'        => string,  // from soop_streamers.label
     *   ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function whitelistedLiveStreams(): array
    {
        $payload    = $this->cachedPayload();
        $broadcasts = $payload['broadcasts'] ?? [];

        // Pull whitelist as [user_id => label] for O(1) lookup.
        $whitelist = \App\Models\Streamer::soop()
            ->get(['user_id', 'label', 'race'])
            ->keyBy('user_id')
            ->map(fn ($s) => ['label' => $s->label, 'race' => $s->race])
            ->all();

        if ($whitelist === []) {
            return [];
        }

        $rows = [];

        foreach ($broadcasts as $b) {
            $userId = $b['user_id'] ?? null;

            if (! is_string($userId) || ! isset($whitelist[$userId])) {
                continue;
            }

            // Skip 19+ and password-protected streams unconditionally.
            // BW community streams should never be either; if they are,
            // it's almost always a misclassified broadcast.
            if ((string) ($b['broad_grade'] ?? '0') === '19') {
                continue;
            }

            if ((string) ($b['is_password'] ?? '0') === '1') {
                continue;
            }

            $rows[] = [
                'platform'    => 'soop',
                'user_id'     => $userId,
                'user_nick'   => (string) ($b['user_nick'] ?? $userId),
                'broad_no'    => (string) ($b['broad_no'] ?? ''),
                'broad_title' => (string) ($b['broad_title'] ?? ''),
                'viewers'     => (int)    ($b['total_view_cnt'] ?? 0),
                'thumbnail'   => $this->normalizeThumbnail($b['broad_thumb'] ?? null),
                'started_at'  => $this->parseDate($b['broad_start'] ?? null),
                'play_url'    => "https://play.sooplive.com/{$userId}/{$b['broad_no']}",
                'label'       => $whitelist[$userId]['label'],
                'race'        => $whitelist[$userId]['race'],  // nullable
            ];
        }

        // Sort by viewers DESC.
        usort($rows, fn ($a, $b) => $b['viewers'] <=> $a['viewers']);

        return $rows;
    }

    /**
     * Return the timestamp of the last successful refresh, or null if
     * the cache has never been populated.
     */
    public function lastFetchedAt(): ?Carbon
    {
        $payload = $this->cachedPayload();

        return isset($payload['fetched_at'])
            ? Carbon::parse($payload['fetched_at'])
            : null;
    }

    /**
     * True when cached data is older than STALE_AFTER_MINUTES.
     * UI uses this to render a "data may be outdated" indicator.
     */
    public function isStale(): bool
    {
        $fetchedAt = $this->lastFetchedAt();

        return $fetchedAt === null
            || $fetchedAt->lt(now()->subMinutes(self::STALE_AFTER_MINUTES));
    }

    /**
     * @return array{fetched_at?: string, broadcasts?: array<int, array<string, mixed>>}
     */
    protected function cachedPayload(): array
    {
        try {
            return Cache::get(self::CACHE_KEY) ?? [];
        } catch (Throwable $e) {
            // If the cache backend is down we don't want the entire page
            // to crash — treat it as "no data".
            Log::warning('SOOP cache read failed', ['message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * SOOP returns thumbnails like "//liveimg.sooplive.com/m/2232".
     * We normalize to an absolute https URL for safe rendering in img tags.
     */
    protected function normalizeThumbnail(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return $url;
    }

    /**
     * Return all live broadcasts NOT in the whitelist.
     *
     * Same output shape as whitelistedLiveStreams() but without `label`/`race`
     * (those come from soop_streamers table which by definition is the whitelist).
     * Sorted by viewers DESC.
     *
     * @return array<int, array<string, mixed>>
     */
    public function otherLiveStreams(): array
    {
        $payload    = $this->cachedPayload();
        $broadcasts = $payload['broadcasts'] ?? [];
        $whitelist  = \App\Models\Streamer::soop()->pluck('user_id')->all();
        $whitelist  = array_flip($whitelist); // O(1) isset() lookup

        $rows = [];

        foreach ($broadcasts as $b) {
            $userId = $b['user_id'] ?? null;

            if (! is_string($userId) || isset($whitelist[$userId])) {
                continue; // skip non-strings and whitelisted (handled separately)
            }

            // Same safety filters as the whitelist path.
            if ((string) ($b['broad_grade'] ?? '0') === '19') {
                continue;
            }

            if ((string) ($b['is_password'] ?? '0') === '1') {
                continue;
            }

            $rows[] = [
                'platform'    => 'soop',
                'user_id'     => $userId,
                'label'       => null,
                'user_nick'   => (string) ($b['user_nick'] ?? $userId),
                'broad_no'    => (string) ($b['broad_no'] ?? ''),
                'broad_title' => (string) ($b['broad_title'] ?? ''),
                'viewers'     => (int)    ($b['total_view_cnt'] ?? 0),
                'thumbnail'   => $this->normalizeThumbnail($b['broad_thumb'] ?? null),
                'started_at'  => $this->parseDate($b['broad_start'] ?? null),
                'play_url'    => "https://play.sooplive.com/{$userId}/{$b['broad_no']}",
            ];
        }

        usort($rows, fn ($a, $b) => $b['viewers'] <=> $a['viewers']);

        return $rows;
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            // SOOP returns KST timestamps without timezone info.
            return Carbon::createFromFormat('Y-m-d H:i:s', $value, 'Asia/Seoul');
        } catch (Throwable) {
            return null;
        }
    }
}