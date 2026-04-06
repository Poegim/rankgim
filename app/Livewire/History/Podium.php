<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class Podium extends Component
{
    #[Computed]
    public function podium()
    {
        // Count months each player held rank 1, 2, or 3
        return DB::table('rating_snapshots')
            ->join('players', 'players.id', '=', 'rating_snapshots.player_id')
            ->whereIn('rating_snapshots.rank', [1, 2, 3])
            ->whereNull('players.player_id') // main players only, no aliases
            ->select(
                'players.id',
                'players.name',
                'players.country_code',
                'players.race',
                'rating_snapshots.rank',
                DB::raw('COUNT(*) as months_count')
            )
            ->groupBy('players.id', 'players.name', 'players.country_code', 'players.race', 'rating_snapshots.rank')
            ->orderBy('rating_snapshots.rank')
            ->orderByDesc('months_count')
            ->get()
            ->groupBy('rank');
    }

    public function render()
    {
        return view('livewire.history.podium');
    }
}