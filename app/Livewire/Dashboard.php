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
     * The closest open forecast matches — used only to decide whether to render
     * the Row 2 "Upcoming matches to predict" widget at all. The widget itself
     * runs its own query with eager-loaded predictions.
     * Returns an empty collection when there is no active season or no open matches.
     */
    #[Computed]
    public function nextForecastMatches(): \Illuminate\Support\Collection
    {
        $season = ForecastSeason::current();

        if (! $season) {
            return collect();
        }

        return ForecastMatch::where('season_id', $season->id)
            ->open()
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}