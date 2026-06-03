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
            // Pull only wallets that have at least one settled prediction
            ->whereHas('predictions', fn($q) => $q->whereIn('result', ['won', 'lost']))
            ->get()
            ->map(function ($wallet) {
                // Profit = sum of payouts minus sum of stakes on settled predictions only
                $settled = $wallet->predictions()
                    ->whereIn('result', ['won', 'lost'])
                    ->selectRaw('SUM(actual_payout) as total_payout, SUM(stake) as total_stake')
                    ->first();

                $wallet->computed_profit = round(
                    (float) ($settled->total_payout ?? 0) - (float) ($settled->total_stake ?? 0),
                    2
                );

                return $wallet;
            })
            ->sortByDesc('computed_profit')
            ->take($this->limit)
            ->values();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard.forecast-standings');
    }
}