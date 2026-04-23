<?php

namespace App\Livewire;

use App\Models\PlayerAchievement;
use App\Models\SystemStat;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentAchievements extends Component
{
    // Achievements excluded from the feed — too common on first game / entry
    protected const EXCLUDED_KEYS = [
        'showing_up',     // every new player — 15 games milestone
        'rookie_mistake', // every new player who loses first game
    ];

    // How many cards to show by default vs. when "Show more" is toggled.
    public const INITIAL_LIMIT  = 8;
    public const EXPANDED_LIMIT = 16;

    // Toggle for the "Show more / Show less" button on the dashboard.
    public bool $showMore = false;

    public function toggleShowMore(): void
    {
        $this->showMore = ! $this->showMore;
    }

    #[Computed]
    public function lastSnapshotDate(): ?string
    {
        // Reuse the already-cached value from system_stats to avoid extra query
        return SystemStat::get('previous_snapshot_date')
            ?? PlayerAchievement::max('unlocked_at');
    }

    #[Computed]
    public function totalPlayers(): int
    {
        return Cache::remember('dashboard.total_players', 3600, function () {
            return \App\Models\PlayerRating::whereHas(
                'player', fn($q) => $q->whereNull('player_id')
            )->count();
        });
    }

    #[Computed]
    public function recentAchievements()
    {
        if (!$this->lastSnapshotDate) return collect();

        // Cache keyed to snapshot date — rotates automatically when new snapshot is taken
        return Cache::remember('dashboard.recent_achievements.' . $this->lastSnapshotDate, 3600, function () {
            $definitions = config('achievements');

            // This query is now inside the cache closure — runs only on cache miss
            $ownerCounts = PlayerAchievement::selectRaw('`key`, MAX(owners_count) as owners_count')
                ->groupBy('key')
                ->pluck('owners_count', 'key');

            return PlayerAchievement::with('player')
                ->whereHas('player', fn($q) => $q->whereNull('player_id'))
                ->whereDate('unlocked_at', $this->lastSnapshotDate)
                ->whereNotIn('key', self::EXCLUDED_KEYS)
                ->get()
                ->map(function ($pa) use ($definitions, $ownerCounts) {
                    $def = $definitions[$pa->key] ?? null;

                    return [
                        'key'          => $pa->key,
                        'name'         => $def ? $def['name'] : $pa->key,
                        'description'  => $def['description'] ?? null,
                        'tier'         => $pa->tier,
                        'category'     => $def['category'] ?? null,
                        'secret'       => $def['secret'] ?? false,
                        'group'        => $def['group'] ?? null,
                        'lore'         => $def['lore'] ?? null,
                        'masked'       => false,
                        'owners_count' => $ownerCounts->get($pa->key, 0),
                        'pct'          => 0,
                        'player_id'    => $pa->player_id,
                        'player_name'  => $pa->player->name,
                        'country'      => $pa->player->country_code,
                        'race'         => $pa->player->race,
                        'unlocked_at'  => $pa->unlocked_at,
                    ];
                })
                ->sortBy(fn($a) => array_search($a['tier'], ['s', 'a', 'b', 'c', 'd']))
                ->values();
        });
    }

    /**
     * Slice of recent achievements limited by the current showMore state —
     * keeps the dashboard grid bounded (8 or 16 cards) instead of dumping the full feed.
     */
    #[Computed]
    public function visibleAchievements()
    {
        $limit = $this->showMore ? self::EXPANDED_LIMIT : self::INITIAL_LIMIT;

        return $this->recentAchievements->take($limit);
    }

    /**
     * Whether to render the Show more / Show less button at all —
     * only when the full feed is bigger than the initial limit.
     */
    #[Computed]
    public function canShowMore(): bool
    {
        return $this->recentAchievements->count() > self::INITIAL_LIMIT;
    }

    public function render()
    {
        return view('livewire.recent-achievements');
    }
}