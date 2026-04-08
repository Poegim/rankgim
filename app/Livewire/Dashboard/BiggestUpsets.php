<?php

namespace App\Livewire\Dashboard;

use App\Models\RatingHistory;
use App\Models\SystemStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BiggestUpsets extends Component
{
    #[Computed]
    public function since(): ?Carbon
    {
        $last = SystemStat::get('last_game_date');
        return $last ? Carbon::parse($last)->subMonths(config('rankgim.inactive_months')) : null;
    }

    #[Computed]
    public function upsets()
    {
        if (!$this->since) return collect();

        return RatingHistory::where('played_at', '>=', $this->since)
            ->where('result', 'win')
            ->whereRaw('rating_before < (SELECT rating_before FROM rating_histories rh2 WHERE rh2.game_id = rating_histories.game_id AND rh2.result = "loss" LIMIT 1) - 100')
            ->with('game.winner', 'game.loser')
            ->orderByRaw('(SELECT rating_before FROM rating_histories rh2 WHERE rh2.game_id = rating_histories.game_id AND rh2.result = "loss" LIMIT 1) - rating_before DESC')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.biggest-upsets');
    }
}