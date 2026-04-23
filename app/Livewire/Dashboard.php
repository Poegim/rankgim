<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\ForecastMatch;
use App\Models\ForecastSeason;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $showMore = false;

    #[Computed]
    public function upcomingEvents()
    {
        return Event::where('starts_at', '>=', now()->subHours(Event::LIVE_WINDOW_HOURS))
            ->with('players')
            ->orderBy('starts_at')
            ->limit(5)
            ->get();
    }

    /**
     * The closest forecast match that is still open for predictions.
     * Used by the Row 2 "Next match to predict" widget.
     * Returns null when there is no active season or no open match — in which case
     * the dashboard layout collapses gracefully (see dashboard.blade.php).
     */
    #[Computed]
    public function nextForecastMatch(): ?ForecastMatch
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

    public function render()
    {
        return view('livewire.dashboard');
    }
}