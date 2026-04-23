<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Hero stats strip shown above the match list.
 *
 * Displays:
 *   - Current faction (currency label + icon)
 *   - Energy (wallet balance)
 *   - Rank in the current season
 *   - Net profit/loss
 *
 * Refreshes when the parent dispatches `wallet-updated`
 * (e.g. after creating/resetting a wallet) or `prediction-placed`.
 */
class StatsBar extends Component
{
    #[Computed]
    public function season(): ?ForecastSeason
    {
        return ForecastSeason::current();
    }

    #[Computed]
    public function wallet(): ?ForecastWallet
    {
        if (! auth()->check() || ! $this->season) {
            return null;
        }

        return ForecastWallet::where('user_id', auth()->id())
            ->where('season_id', $this->season->id)
            ->first();
    }

    /**
     * Compute every wallet's profit and find where the current user sits.
     * Returned as ['rank' => int|null, 'total' => int].
     */
    #[Computed]
    public function rankInfo(): array
    {
        if (! $this->season) {
            return ['rank' => null, 'total' => 0];
        }

        $wallets = ForecastWallet::where('season_id', $this->season->id)
            ->whereHas('predictions', fn($q) => $q->whereIn('result', ['won', 'lost']))
            ->get()
            ->map(function ($wallet) {
                $settled = $wallet->predictions()
                    ->whereIn('result', ['won', 'lost'])
                    ->get();
                $wallet->computed_profit = $settled->sum('actual_payout') - $settled->sum('stake');
                return $wallet;
            })
            ->sortByDesc('computed_profit')
            ->values();

        $rank = null;
        if (auth()->check()) {
            foreach ($wallets as $i => $w) {
                if ($w->user_id === auth()->id()) {
                    $rank = $i + 1;
                    break;
                }
            }
        }

        return ['rank' => $rank, 'total' => $wallets->count()];
    }

    // Force refresh from child events
    #[On('wallet-updated')]
    #[On('prediction-placed')]
    #[On('match-settled')]
    public function refresh(): void
    {
        unset($this->wallet, $this->rankInfo);
    }

    public function openCurrencyModal(): void
    {
        $this->dispatch('open-currency-modal');
    }

    public function resetWallet(): void
    {
        $this->dispatch('reset-wallet');
    }

    public function render()
    {
        return view('livewire.forecast.stats-bar');
    }
}