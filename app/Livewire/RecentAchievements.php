<?php

namespace App\Livewire;

use App\Models\PlayerAchievement;
use App\Models\SystemStat;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentAchievements extends Component
{
    // Achievements excluded from the feed — too common on first game / entry
    protected const EXCLUDED_KEYS = [
        'showing_up',     // every new player — 15 games milestone
        'rookie_mistake', // every new player who loses first game
    ];

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
        return \App\Models\PlayerRating::whereHas(
            'player', fn($q) => $q->whereNull('player_id')
        )->count();
    }

    #[Computed]
    public function recentAchievements()
    {
        if (!$this->lastSnapshotDate) return collect();

        $definitions = config('achievements');

        // Load owners_count per key in a single query to avoid N+1
        $ownerCounts = PlayerAchievement::selectRaw('`key`, MAX(owners_count) as owners_count')
            ->groupBy('key')
            ->pluck('owners_count', 'key');

        return PlayerAchievement::with('player')
            // Only main players (not aliases)
            ->whereHas('player', fn($q) => $q->whereNull('player_id'))
            // Only achievements from the latest snapshot run
            ->whereDate('unlocked_at', $this->lastSnapshotDate)
            // Exclude high-noise onboarding achievements that spam the feed
            ->whereNotIn('key', self::EXCLUDED_KEYS)
            ->get()
            ->map(function ($pa) use ($definitions, $ownerCounts) {
                $def = $definitions[$pa->key] ?? null;

                // Build array matching the shape expected by x-achievement-card
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
                    // Extra fields used in the blade for player link
                    'player_id'    => $pa->player_id,
                    'player_name'  => $pa->player->name,
                    'country'      => $pa->player->country_code,
                    'race'         => $pa->player->race,
                    'unlocked_at'  => $pa->unlocked_at,
                ];
            })
            // Sort S → A → B → C → D
            ->sortBy(fn($a) => array_search($a['tier'], ['s', 'a', 'b', 'c', 'd']))
            ->values();
    }

    public function render()
    {
        return view('livewire.recent-achievements');
    }
}