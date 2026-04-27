<?php

namespace App\Livewire\Dashboard;

use App\Models\ForecastMatch;
use App\Models\ForecastSeason;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NextForecastMatch extends Component
{
    // How many closest upcoming matches to show on the dashboard.
    public const LIMIT = 6;

    #[Computed]
    public function matches(): Collection
    {
        $season = ForecastSeason::current();

        if (! $season) {
            return collect();
        }

        $matches = ForecastMatch::with([
                'playerA',
                'playerB',
                'event',
                // Load ALL pending predictions for these matches so we can compute
                // community sentiment bars (% money on A vs B). The per-user "Your pick"
                // label is derived from this same relation by filtering in PHP.
                'predictions' => fn($q) => $q->where('result', 'pending'),
            ])
            ->where('season_id', $season->id)
            ->open()
            ->orderBy('scheduled_at')
            ->limit(self::LIMIT)
            ->get();

        // Pre-compute stake totals per side for each match so the blade stays dumb.
        return $matches->map(function (ForecastMatch $match) {
            $stakeA = 0.0;
            $stakeB = 0.0;

            foreach ($match->predictions as $prediction) {
                $isSideA = $match->match_type === 'foreigner'
                    ? $prediction->pick_player_id === $match->player_a_id
                    : $prediction->pick_side === 'a';

                if ($isSideA) {
                    $stakeA += (float) $prediction->stake;
                } else {
                    $stakeB += (float) $prediction->stake;
                }
            }

            $total = $stakeA + $stakeB;

            // Attach computed fields directly onto the model for the blade's convenience.
            // Not persisted — just in-memory for rendering.
            $match->stake_a         = round($stakeA, 2);
            $match->stake_b         = round($stakeB, 2);
            $match->stake_total     = round($total, 2);
            $match->stake_a_percent = $total > 0 ? round(($stakeA / $total) * 100) : 50;
            $match->stake_b_percent = $total > 0 ? round(($stakeB / $total) * 100) : 50;
            $match->picks_count     = $match->predictions->count();

            return $match;
        });
    }

    public function render()
    {
        return view('livewire.dashboard.next-forecast-match');
    }
}