<?php

namespace App\Livewire\Dashboard;

use App\Models\RatingHistory;
use App\Models\SystemStat;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MostDominant extends Component
{
    #[Computed]
    public function since(): ?Carbon
    {
        $last = SystemStat::get('last_game_date');
        return $last ? Carbon::parse($last)->subMonths(config('rankgim.inactive_months')) : null;
    }

    #[Computed]
    public function players()
    {
        if (!$this->since) return collect();

        return RatingHistory::where('played_at', '>=', $this->since)
            ->selectRaw('player_id, count(*) as total, sum(result = "win") as wins')
            ->groupBy('player_id')
            ->having('total', '>=', 15)
            ->orderByRaw('wins / total DESC')
            ->with('player')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.most-dominant');
    }
}