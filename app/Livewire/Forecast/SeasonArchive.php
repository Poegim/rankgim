<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastSeason;
use App\Models\ForecastSeasonSnapshot;
use App\Models\ForecastWallet;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Displays a list of all closed forecast seasons with their final standings.
 *
 * Shown on the Forecast page when there is no active season, or as a
 * persistent "Archive" tab alongside the active season tabs.
 */
class SeasonArchive extends Component
{
    /** ID of the season whose standings are currently expanded. */
    public ?int $expandedSeasonId = null;

    public function mount(): void
    {
        // Auto-expand the most recently closed season on first load.
        $latest = $this->seasons->first();

        if ($latest) {
            $this->expandedSeasonId = $latest->id;
        }
    }

    // ── Computed ──────────────────────────────────────

    /** All closed (inactive) seasons, newest first. */
    #[Computed]
    public function seasons(): Collection
    {
        return ForecastSeason::where('is_active', false)
            ->latest('ends_at')
            ->get();
    }

    /**
     * Final standings for the expanded season.
     *
     * Returns snapshots with the user relationship eager-loaded,
     * sorted by rank ascending (rank 1 = winner).
     */
    #[Computed]
    public function standings(): Collection
    {
        if (! $this->expandedSeasonId) {
            return collect();
        }

        return ForecastSeasonSnapshot::where('season_id', $this->expandedSeasonId)
            ->with('user')
            ->orderBy('rank')
            ->get();
    }

    // ── Actions ───────────────────────────────────────

    /** Toggle expanded season — clicking the same season collapses it. */
    public function toggleSeason(int $seasonId): void
    {
        $this->expandedSeasonId = $this->expandedSeasonId === $seasonId
            ? null
            : $seasonId;

        // Clear computed cache so standings reload for the new season.
        unset($this->standings);
    }

    public function render()
    {
        return view('livewire.forecast.season-archive');
    }
}