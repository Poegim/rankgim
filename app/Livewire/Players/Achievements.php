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
                    'key'         => $achievement->key,
                    'name'        => $def['name'],
                    'description' => $def['description'],
                    'tier'        => $achievement->tier,
                    'category'    => $def['category'],
                    'secret'      => $def['secret'],
                    'value'       => $achievement->value,
                    'unlocked_at' => $achievement->unlocked_at,
                ];
            })
            ->filter()
            // Sort by tier S→D, then by name
            ->sortBy([
                fn($a) => array_search($a['tier'], ['s', 'a', 'b', 'c', 'd']),
                fn($a) => $a['name'],
            ])
            ->groupBy('category');
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