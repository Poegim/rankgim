<?php

namespace App\Livewire\History;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Top10Time extends Component
{
    #[Computed]
    public function leaders()
    {
        // Count months each player appeared in top 10 across all snapshots
        return DB::table('rating_snapshots')
            ->join('players', 'players.id', '=', 'rating_snapshots.player_id')
            ->where('rating_snapshots.rank', '<=', 10)
            ->whereNull('players.player_id')
            ->selectRaw('players.id, players.name, players.country_code, players.race, COUNT(*) as months_count')
            ->groupBy('players.id', 'players.name', 'players.country_code', 'players.race')
            ->orderByDesc('months_count')
            ->get();
    }

    public function render()
    {
        return view('livewire.history.top10-time');
    }
}