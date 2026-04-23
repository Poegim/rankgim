<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Leaderboard view — podium for top 3, compact list for the rest.
 * Refreshes on prediction/match-settled events from siblings.
 */
class Standings extends Component
{
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

        return ForecastWallet::where('season_id', $this->season->id)
            ->whereHas('predictions', fn($q) => $q->whereIn('result', ['won', 'lost']))
            ->with('user')
            ->get()
            ->map(function ($wallet) {
                $settled = $wallet->predictions()
                    ->whereIn('result', ['won', 'lost'])
                    ->get();

                // NB: name it computed_profit (not profit) — the model has a
                // profit() method and Eloquent will otherwise probe it as a
                // relationship on __get.
                $wallet->computed_profit = round(
                    $settled->sum('actual_payout') - $settled->sum('stake'),
                    2
                );
                $wallet->settled_count = $settled->count();
                $wallet->won_count     = $settled->where('result', 'won')->count();

                return $wallet;
            })
            ->sortByDesc('computed_profit')
            ->values();
    }

    #[On('prediction-placed')]
    #[On('match-settled')]
    #[On('wallet-updated')]
    public function refresh(): void
    {
        unset($this->ranking);
    }

    public function render()
    {
        return view('livewire.forecast.standings');
    }
}