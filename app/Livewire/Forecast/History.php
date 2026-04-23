<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastPrediction;
use App\Models\ForecastSeason;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * User's personal prediction history for the active season.
 *
 * Shows:
 *   - Summary stats (total forecasts, accuracy, profit, streak)
 *   - Profit-over-time chart (cumulative net, one point per settled prediction)
 *   - Table of every prediction (won / lost / refunded / pending) with all
 *     the details (match, stake, odds × multiplier, bonus, payout)
 */
class History extends Component
{
    #[Computed]
    public function season(): ?ForecastSeason
    {
        return ForecastSeason::current();
    }

    /**
     * All of the current user's predictions for the active season,
     * newest first. Eager-loaded with match + picked player so the
     * view doesn't hit the DB per row.
     */
    #[Computed]
    public function predictions(): Collection
    {
        if (! auth()->check() || ! $this->season) {
            return collect();
        }

        return ForecastPrediction::where('user_id', auth()->id())
            ->whereHas('match', fn($q) => $q->where('season_id', $this->season->id))
            ->with([
                'match.playerA',
                'match.playerB',
                'match.winner',
                'pickedPlayer',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Overall summary numbers for the header cards.
     */
    #[Computed]
    public function stats(): array
    {
        $all = $this->predictions;
        $settled = $all->whereIn('result', ['won', 'lost']);

        $totalStake  = (float) $settled->sum('stake');
        $totalPayout = (float) $settled->sum('actual_payout');
        $profit      = round($totalPayout - $totalStake, 2);

        $wonCount  = $settled->where('result', 'won')->count();
        $lostCount = $settled->where('result', 'lost')->count();
        $totalSettled = $wonCount + $lostCount;

        // Current streak — walk back from newest settled prediction
        // until the result flips or runs out. Returns ['won'|'lost', count].
        $streakType = null;
        $streakLen  = 0;
        foreach ($settled->sortByDesc('created_at')->values() as $p) {
            if ($streakType === null) {
                $streakType = $p->result;
                $streakLen  = 1;
                continue;
            }
            if ($p->result === $streakType) {
                $streakLen++;
            } else {
                break;
            }
        }

        // Longest winning streak ever this season
        $longestWin = 0;
        $running = 0;
        foreach ($settled->sortBy('created_at')->values() as $p) {
            if ($p->result === 'won') {
                $running++;
                $longestWin = max($longestWin, $running);
            } else {
                $running = 0;
            }
        }

        return [
            'total'         => $all->count(),
            'settled'       => $totalSettled,
            'pending'       => $all->where('result', 'pending')->count(),
            'won'           => $wonCount,
            'lost'          => $lostCount,
            'accuracy'      => $totalSettled > 0 ? round($wonCount / $totalSettled * 100, 1) : 0,
            'profit'        => $profit,
            'total_stake'   => $totalStake,
            'total_payout'  => $totalPayout,
            'streak_type'   => $streakType,
            'streak_len'    => $streakLen,
            'longest_win'   => $longestWin,
        ];
    }

    /**
     * Points for the profit-over-time chart.
     * Each settled prediction contributes its net (payout - stake) to a
     * running cumulative total, keyed by when it was settled.
     * Format: [['x' => 'YYYY-MM-DD HH:mm', 'y' => cumulative_profit], ...]
     */
    #[Computed]
    public function chartData(): array
    {
        $settled = $this->predictions
            ->whereIn('result', ['won', 'lost'])
            ->sortBy('updated_at') // settlement timestamp lives in updated_at
            ->values();

        $cumulative = 0.0;
        $points = [];

        foreach ($settled as $p) {
            $net = ((float) $p->actual_payout) - ((float) $p->stake);
            $cumulative += $net;

            $points[] = [
                'x' => $p->updated_at->format('Y-m-d H:i'),
                'y' => round($cumulative, 2),
            ];
        }

        return $points;
    }

    #[On('prediction-placed')]
    #[On('match-settled')]
    public function refresh(): void
    {
        unset($this->predictions, $this->stats, $this->chartData);
    }

    public function render()
    {
        return view('livewire.forecast.history');
    }
}