<?php

namespace App\Livewire\Dashboard;

use App\Models\PlayerRating;
use App\Models\RatingSnapshot;
use App\Models\SystemStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TopPlayers extends Component
{
    #[Computed]
    public function since(): ?Carbon
    {
        $last = SystemStat::get('last_game_date');
        return $last ? Carbon::parse($last)->subMonths(config('rankgim.inactive_months')) : null;
    }

    #[Computed]
    public function previousSnapshotDate(): ?string
    {
        return SystemStat::get('previous_snapshot_date');
    }

    #[Computed]
    public function players()
    {
        if (!$this->since) return collect();

        // Cache for 15 minutes — top 10 doesn't change by the second
        return Cache::remember('dashboard.top_players', 3600, function () {
            $ratings = PlayerRating::with('player')
                ->whereHas('player', fn($q) => $q->whereNull('player_id'))
                ->where('games_played', '>=', 15)
                ->whereHas('playerStat', fn($q) => $q->where('last_played_at', '>=', $this->since))
                ->orderByDesc('rating')
                ->limit(10)
                ->get();

            $snapshots = RatingSnapshot::whereIn('player_id', $ratings->pluck('player_id'))
                ->where('snapshot_date', $this->previousSnapshotDate)
                ->get()
                ->keyBy('player_id');

            return $ratings->map(function ($row) use ($snapshots) {
                $row->prev_rating = $snapshots->get($row->player_id)?->rating;
                return $row;
            });
        });
    }

    public function render()
    {
        return view('livewire.dashboard.top-players');
    }
}