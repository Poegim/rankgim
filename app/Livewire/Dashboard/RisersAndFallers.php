<?php

namespace App\Livewire\Dashboard;

use App\Models\SystemStat;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RisersAndFallers extends Component
{
    #[Computed]
    public function previousSnapshotDate(): ?string
    {
        return SystemStat::get('previous_snapshot_date');
    }

    #[Computed]
    public function risers()
    {
        if (!$this->previousSnapshotDate) return collect();

        return DB::table('player_ratings')
            ->join('players', 'players.id', '=', 'player_ratings.player_id')
            ->join('rating_snapshots', function ($join) {
                $join->on('rating_snapshots.player_id', '=', 'player_ratings.player_id')
                     ->where('rating_snapshots.snapshot_date', $this->previousSnapshotDate);
            })
            ->selectRaw('players.id, players.name, players.country_code, players.race, (player_ratings.rating - rating_snapshots.rating) as rating_change')
            ->orderByRaw('rating_change DESC')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function fallers()
    {
        if (!$this->previousSnapshotDate) return collect();

        return DB::table('player_ratings')
            ->join('players', 'players.id', '=', 'player_ratings.player_id')
            ->join('rating_snapshots', function ($join) {
                $join->on('rating_snapshots.player_id', '=', 'player_ratings.player_id')
                     ->where('rating_snapshots.snapshot_date', $this->previousSnapshotDate);
            })
            ->selectRaw('players.id, players.name, players.country_code, players.race, (player_ratings.rating - rating_snapshots.rating) as rating_change')
            ->orderByRaw('rating_change ASC')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.risers-and-fallers');
    }
}