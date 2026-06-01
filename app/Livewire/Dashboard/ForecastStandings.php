<?php

namespace App\Livewire\Dashboard;

use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ForecastStandings extends Component
{
    /**
     * Number of top players to show in the dashboard widget.
     */
    public int $limit = 5;

    #[Computed]
    public function season(): ?ForecastSeason
    {
        return ForecastSeason::current();
    }

    #[Computed]
    public function ranking(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return ForecastWallet::query()
            ->where('season_id', $this->season->id)
            ->with('user')
            ->withCount(['predictions as settled_count' => fn($q) => $q->whereIn('result', ['won', 'lost', 'refunded'])])
            ->withCount(['predictions as won_count' => fn($q) => $q->where('result', 'won')])
            ->orderByDesc('balance')
            ->limit($this->limit)
            ->get()
            ->each(fn($w) => $w->computed_profit = $w->balance - 50);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.forecast-standings');
    }
}