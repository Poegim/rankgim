<?php

namespace App\Livewire\History;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RankedPlayersChart extends Component
{
    #[Computed]
    public function data()
    {
        // Changes only when a new snapshot is built — 1 hour TTL is safe
        return Cache::remember('history.ranked_players_chart', 3600, function () {
            return DB::table('rating_snapshots')
                ->selectRaw('snapshot_date, COUNT(*) as player_count')
                ->groupBy('snapshot_date')
                ->orderBy('snapshot_date')
                ->get()
                ->map(fn($row) => [
                    'date'  => $row->snapshot_date,
                    'count' => (int) $row->player_count,
                ]);
        });
    }

    public function render()
    {
        return view('livewire.history.ranked-players-chart');
    }
}