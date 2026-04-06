<?php

namespace App\Livewire\Players;

use App\Models\PlayerAchievement;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Achievements extends Component
{
    public int $playerId;

    #[Computed]
    public function achievements()
    {
        $definitions = config('achievements');

        return PlayerAchievement::where('player_id', $this->playerId)
            ->get()
            ->map(function ($achievement) use ($definitions) {
                $def = $definitions[$achievement->key] ?? null;
                if (!$def) return null;

                return [
                    'key'          => $achievement->key,
                    'name'         => $def['name'],
                    'description'  => $def['description'],
                    'tier'         => $achievement->tier,
                    'category'     => $def['category'],
                    'secret'       => $def['secret'],
                    'group'        => $def['group'] ?? null,
                    'value'        => $achievement->value,
                    'unlocked_at'  => $achievement->unlocked_at,
                    'owners_count' => $achievement->owners_count,
                ];
            })
            ->filter()
            // Sort S→D first so highest tier wins when grouping
            ->sortBy(fn($a) => array_search($a['tier'], ['s', 'a', 'b', 'c', 'd']))
            // Group by group key — keep only highest tier per group
            ->groupBy(fn($a) => $a['group'] ?? $a['key'])
            ->map(fn($group) => $group->first())
            ->values();
    }

#[Computed]
public function debug()
{
    $definitions = config('achievements');
    $unlocked = PlayerAchievement::where('player_id', $this->playerId)
        ->pluck('key')
        ->flip();

    $grouped = collect($definitions)
        ->groupBy(fn($def, $key) => $def['group'] ?? $key)
        ->map(fn($group) => $group->keys()->toArray());

    return [
        'definitions_count' => count($definitions),
        'unlocked_count'    => count($unlocked),
        'grouped_count'     => $grouped->count(),
        'first_group'       => $grouped->first(),
        'unlocked_keys'     => $unlocked->keys()->take(5)->toArray(),
    ];
}

#[Computed]
public function categoryCounts(): array
{
    $definitions = config('achievements');
    $unlocked    = PlayerAchievement::where('player_id', $this->playerId)
        ->pluck('key')
        ->flip();

    $counts = [];

    // Group achievement keys manually to preserve keys
    $grouped = [];
    foreach ($definitions as $key => $def) {
        $groupKey = $def['group'] ?? $key;
        $grouped[$groupKey][] = $key;
    }

    foreach ($grouped as $groupKey => $keys) {
        $firstKey = $keys[0];
        if (!isset($definitions[$firstKey])) continue;

        $cat = $definitions[$firstKey]['category'];
        if (!isset($counts[$cat])) {
            $counts[$cat] = ['unlocked' => 0, 'total' => 0];
        }
        $counts[$cat]['total'] += count($keys);

        // Count how many tiers in this group the player has unlocked
        foreach ($keys as $k) {
            if (isset($unlocked[$k])) {
                $counts[$cat]['unlocked']++;
            }
        }
    }
    

    return $counts;
}

    #[Computed]
    public function totalPlayers(): int
    {
        return \App\Models\PlayerRating::whereHas(
            'player', fn($q) => $q->whereNull('player_id')
        )->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return count(config('achievements'));
    }

    public function render()
    {
        return view('livewire.players.achievements');
    }
}