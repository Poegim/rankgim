<?php

namespace App\Services\Streams;

use App\Models\User;
use App\Models\UserFavoriteStreamer;
use App\Services\Soop\SoopLiveStatusService;
use App\Services\Twitch\TwitchLiveStatusService;
use Illuminate\Support\Carbon;

/**
 * Aggregator that fuses SOOP and Twitch live-stream data into a single
 * platform-agnostic feed for the UI.
 *
 * Each underlying service still owns its own cache and refresh cadence.
 * This class only does the merge, optional platform-scoping, and (when a user
 * is supplied) favorite-aware sorting/promotion.
 *
 * Platform argument contract:
 *   - null      → both platforms merged
 *   - 'soop'    → only SOOP
 *   - 'twitch'  → only Twitch
 *
 * User argument contract:
 *   - null      → no favorites injected, no promotion (guest view)
 *   - User      → rows get `is_favorite` boolean; non-whitelisted favorites get
 *                 promoted from "Other" into "Featured" (at the top)
 *
 * Output row shape (per row, after this aggregator runs):
 *   [
 *     'platform'    => 'soop'|'twitch',
 *     'user_id'     => string,
 *     'user_nick'   => string,
 *     'broad_title' => string,
 *     'viewers'     => int,
 *     'thumbnail'   => string|null,
 *     'started_at'  => Carbon|null,
 *     'play_url'    => string|null,
 *     'label'       => string|null,
 *     'race'        => string|null,
 *     'is_favorite' => bool,
 *     // SOOP also has 'broad_no'.
 *   ]
 */
class LiveStreamsService
{
    public const PLATFORM_SOOP   = 'soop';
    public const PLATFORM_TWITCH = 'twitch';

    public function __construct(
        private readonly SoopLiveStatusService $soop,
        private readonly TwitchLiveStatusService $twitch,
    ) {
    }

    /**
     * Whitelisted (admin-curated) + favorited (per-user promoted) streams.
     *
     * Sort order: favorites first (by viewers DESC), then non-favorites (by viewers DESC).
     *
     * @return array<int, array<string, mixed>>
     */
    public function featuredStreams(?string $platform = null, ?User $user = null): array
    {
        $favorites = $this->loadFavoriteSet($user);

        // Start with admin whitelist from each requested platform.
        $featured = [];

        if ($platform === null || $platform === self::PLATFORM_SOOP) {
            $featured = array_merge($featured, $this->soop->whitelistedLiveStreams());
        }

        if ($platform === null || $platform === self::PLATFORM_TWITCH) {
            $featured = array_merge($featured, $this->twitch->whitelistedLiveStreams());
        }

        // Annotate every row up front so the view can reliably read is_favorite.
        $featured = $this->annotateFavorites($featured, $favorites);

        // Promote favorited NON-whitelisted streams from "Other" into Featured.
        // (per product decision: favorites win over whitelist)
        if ($user !== null && $favorites !== []) {
            $promoted = $this->promotedFavorites($platform, $favorites, $featured);
            $featured = array_merge($featured, $promoted);
        }

        return $this->sortFavoritesFirstThenViewers($featured);
    }

    /**
     * Non-whitelisted, non-favorited live streams — the long-tail "other" feed.
     *
     * When a user is logged in, their favorited non-whitelisted streams are
     * excluded here (they've already been promoted into Featured).
     *
     * @return array<int, array<string, mixed>>
     */
    public function otherStreams(?string $platform = null, ?User $user = null): array
    {
        $favorites = $this->loadFavoriteSet($user);
        $rows      = [];

        if ($platform === null || $platform === self::PLATFORM_SOOP) {
            $rows = array_merge($rows, $this->soop->otherLiveStreams());
        }

        if ($platform === null || $platform === self::PLATFORM_TWITCH) {
            $rows = array_merge($rows, $this->twitch->otherLiveStreams());
        }

        // Annotate with is_favorite (will be true for favorited non-whitelisted
        // entries that we then strip below — but doing it here keeps the row
        // shape consistent if a caller bypasses the strip).
        $rows = $this->annotateFavorites($rows, $favorites);

        // Remove favorited non-whitelisted streams — they live in Featured now.
        if ($favorites !== []) {
            $rows = array_values(array_filter(
                $rows,
                fn (array $row) => ! ($row['is_favorite'] ?? false),
            ));
        }

        return $this->sortByViewersDesc($rows);
    }

    /**
     * Oldest of the per-platform fetch timestamps — the freshness floor.
     */
    public function lastFetchedAt(): ?Carbon
    {
        $soopAt   = $this->soop->lastFetchedAt();
        $twitchAt = $this->twitch->lastFetchedAt();

        if ($soopAt === null && $twitchAt === null) {
            return null;
        }

        if ($soopAt === null) {
            return $this->toCarbon($twitchAt);
        }

        if ($twitchAt === null) {
            return $this->toCarbon($soopAt);
        }

        return $this->toCarbon($soopAt->lt($twitchAt) ? $soopAt : $twitchAt);
    }

    public function isStale(): bool
    {
        return $this->soop->isStale() || $this->twitch->isStale();
    }

    // ── Internals ────────────────────────────────────────────────

    /**
     * Pull the current user's favorites into a flat set keyed by
     * "{platform}:{streamer_user_id}" — O(1) lookups during annotation.
     *
     * @return array<string, true>
     */
    private function loadFavoriteSet(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        return UserFavoriteStreamer::query()
            ->where('user_id', $user->id)
            ->get(['platform', 'streamer_user_id'])
            ->mapWithKeys(fn ($fav) => [
                $this->favoriteKey($fav->platform, $fav->streamer_user_id) => true,
            ])
            ->all();
    }

    /**
     * Inject `is_favorite` boolean into each row using the precomputed set.
     *
     * @param  array<int, array<string, mixed>> $rows
     * @param  array<string, true>              $favoriteSet
     * @return array<int, array<string, mixed>>
     */
    private function annotateFavorites(array $rows, array $favoriteSet): array
    {
        if ($favoriteSet === []) {
            // Still set the key so the view template can always read it.
            foreach ($rows as &$row) {
                $row['is_favorite'] = false;
            }
            return $rows;
        }

        foreach ($rows as &$row) {
            $key                 = $this->favoriteKey($row['platform'] ?? '', $row['user_id'] ?? '');
            $row['is_favorite']  = isset($favoriteSet[$key]);
        }

        return $rows;
    }

    /**
     * Find favorited streams currently in the "Other" pool and return them
     * for promotion into Featured. Skips entries already in $alreadyFeatured
     * (whitelisted + favorited streamers don't need to be re-promoted).
     *
     * @param  array<string, true>              $favoriteSet
     * @param  array<int, array<string, mixed>> $alreadyFeatured
     * @return array<int, array<string, mixed>>
     */
    private function promotedFavorites(?string $platform, array $favoriteSet, array $alreadyFeatured): array
    {
        // Build a set of keys already in Featured so we don't dupe.
        $featuredKeys = [];
        foreach ($alreadyFeatured as $row) {
            $featuredKeys[$this->favoriteKey($row['platform'] ?? '', $row['user_id'] ?? '')] = true;
        }

        $candidates = [];

        if ($platform === null || $platform === self::PLATFORM_SOOP) {
            $candidates = array_merge($candidates, $this->soop->otherLiveStreams());
        }

        if ($platform === null || $platform === self::PLATFORM_TWITCH) {
            $candidates = array_merge($candidates, $this->twitch->otherLiveStreams());
        }

        $promoted = [];
        foreach ($candidates as $row) {
            $key = $this->favoriteKey($row['platform'] ?? '', $row['user_id'] ?? '');

            if (! isset($favoriteSet[$key])) {
                continue; // not favorited
            }

            if (isset($featuredKeys[$key])) {
                continue; // already in Featured via whitelist
            }

            $row['is_favorite'] = true;
            $promoted[] = $row;
        }

        return $promoted;
    }

    /**
     * Composite key used for O(1) favorite lookups.
     * Twitch user_ids are already lowercased upstream; SOOP user_ids are
     * case-sensitive but consistent across the API + DB, so no normalization
     * needed here.
     */
    private function favoriteKey(string $platform, string $userId): string
    {
        return $platform . ':' . $userId;
    }

    /**
     * Sort key: favorites first (1 = pinned, 0 = normal), then viewers DESC.
     *
     * @param  array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function sortFavoritesFirstThenViewers(array $rows): array
    {
        usort($rows, function (array $a, array $b) {
            $favCompare = (int) ($b['is_favorite'] ?? false) <=> (int) ($a['is_favorite'] ?? false);
            if ($favCompare !== 0) {
                return $favCompare;
            }
            return ($b['viewers'] ?? 0) <=> ($a['viewers'] ?? 0);
        });

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function sortByViewersDesc(array $rows): array
    {
        usort($rows, fn ($a, $b) => ($b['viewers'] ?? 0) <=> ($a['viewers'] ?? 0));
        return $rows;
    }

    private function toCarbon(\DateTimeInterface $value): Carbon
    {
        return Carbon::instance($value);
    }
}