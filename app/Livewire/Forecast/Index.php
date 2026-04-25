<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use App\Services\ForecastService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Top-level Forecast page.
 *
 * Responsibilities (and nothing more):
 *   - Hold the active main-tab state (forecasts / standings / history)
 *   - Show the season header + admin buttons (start / close season)
 *   - Render the currency-pick modal and new-season modal
 *   - Render the main tab buttons
 *
 * Everything else is delegated to child Livewire components:
 *   - StatsBar   — hero stats strip
 *   - MatchList  — match cards + bet/settle/add/edit/delete modals
 *   - Standings  — leaderboard (podium + rest)
 *   - History    — user's pick history with a profit chart
 */
class Index extends Component
{
    /**
     * Active main tab.
     * Kept in the URL so refreshing / sharing a link preserves the view.
     */
    #[Url]
    public string $tab = 'forecasts'; // forecasts | standings | history | archive

    // Sub-view inside the Forecasts tab: 'open' or 'settled'.
    // Stays in the URL for the same reason as $tab.
    #[Url]
    public string $view = 'open'; // open | settled

    // ── Currency selection modal ──────────────────────
    public bool $showCurrencyModal = false;
    public string $selectedCurrency = 'minerals';

    // ── New season modal ──────────────────────────────
    public bool $showSeasonModal = false;
    public string $seasonName = '';
    public string $seasonStartsAt = '';

    public function mount(): void
    {
        $this->seasonStartsAt = now()->format('Y-m-d\TH:i');
    }

    // ── Computed ──────────────────────────────────────

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

    // ── Tab switching ─────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function setView(string $view): void
    {
        // Convenience: calling setView('open') or setView('settled') from
        // child components also snaps us back to the forecasts tab.
        $this->view = $view;
        $this->tab  = 'forecasts';
    }

    // ── Currency / wallet creation ────────────────────

    public function openCurrencyModal(): void
    {
        $this->showCurrencyModal = true;
    }

    public function createWallet(): void
    {
        $this->validate(['selectedCurrency' => 'required|in:minerals,khaydarin,biomass,credits']);

        app(ForecastService::class)->getOrCreateWallet(auth()->user(), $this->selectedCurrency);

        $this->showCurrencyModal = false;
        unset($this->wallet);

        // Tell children to refresh
        $this->dispatch('wallet-updated');
    }

    public function resetWallet(): void
    {
        abort_if(! $this->wallet, 403);
        app(ForecastService::class)->resetWallet($this->wallet);
        unset($this->wallet);

        $this->dispatch('wallet-updated');
    }

    // ── Season management (admin) ─────────────────────

    public function openSeasonModal(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        $this->showSeasonModal = true;
    }

    public function createSeason(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);

        $this->validate([
            'seasonName'     => 'required|string|max:100',
            'seasonStartsAt' => 'required|date',
        ]);

        ForecastSeason::create([
            'name'       => $this->seasonName,
            'starts_at'  => $this->seasonStartsAt,
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        $this->showSeasonModal = false;
        $this->seasonName = '';
        unset($this->season);
    }

    public function closeSeason(): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);
        app(ForecastService::class)->closeSeason($this->season);
        unset($this->season, $this->wallet);
    }

    public function render()
    {
        return view('livewire.forecast.index');
    }
}