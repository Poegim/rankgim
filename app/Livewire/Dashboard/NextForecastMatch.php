<?php

namespace App\Livewire\Dashboard;

use App\Models\ForecastMatch;
use App\Models\ForecastPrediction;
use App\Models\ForecastSeason;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NextForecastMatch extends Component
{
    #[Computed]
    public function match(): ?ForecastMatch
    {
        $season = ForecastSeason::current();

        if (! $season) {
            return null;
        }

        return ForecastMatch::with(['playerA', 'playerB', 'event'])
            ->where('season_id', $season->id)
            ->open()
            ->orderBy('scheduled_at')
            ->first();
    }

    #[Computed]
    public function userPrediction(): ?ForecastPrediction
    {
        if (! auth()->check() || ! $this->match) {
            return null;
        }

        return ForecastPrediction::where('user_id', auth()->id())
            ->where('match_id', $this->match->id)
            ->first();
    }

    public function render()
    {
        return view('livewire.dashboard.next-forecast-match');
    }
}