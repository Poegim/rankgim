<?php

namespace App\Livewire\Dashboard;

use App\Models\SystemStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HotStreaks extends Component
{
    #[Computed]
    public function since(): ?Carbon
    {
        $last = SystemStat::get('last_game_date');
        return $last ? Carbon::parse($last)->subMonths(config('rankgim.inactive_months')) : null;
    }

    #[Computed]
    public function streaks()
    {
        if (!$this->since) return collect();

        return DB::table('player_stats')
            ->join('players', 'players.id', '=', 'player_stats.player_id')
            ->where('player_stats.last_played_at', '>=', $this->since)
            ->where('player_stats.current_streak', '>', 0)
            ->orderByDesc('player_stats.current_streak')
            ->limit(5)
            ->select('players.id', 'players.name', 'players.country_code', 'players.race', 'player_stats.current_streak as streak')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.hot-streaks');
    }
}