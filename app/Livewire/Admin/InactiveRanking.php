<?php

namespace App\Livewire\Admin;

use App\Models\PlayerRating;
use App\Models\PlayerStat;
use Livewire\Attributes\Computed;
use Livewire\Component;

class InactiveRanking extends Component
{
    /**
     * Build the ranking of inactive players ordered by their ELO rating descending.
     *
     * Inactive = has 15+ games but last_played_at is older than the configured threshold.
     * This mirrors the same inactivity logic used in Rankings\Index and Players\Show.
     */
    #[Computed]
    public function players(): \Illuminate\Support\Collection
    {
        $inactiveMonths = config('rankgim.inactive_months');

        // Determine the cutoff date relative to the most recent game ever played
        $lastGame = \App\Models\RatingHistory::max('played_at');
        if (! $lastGame) {
            return collect();
        }

        $cutoff = \Carbon\Carbon::parse($lastGame)
            ->subMonths($inactiveMonths)
            ->toDateString();

        // Fetch all player ratings that meet the games threshold
        return PlayerRating::query()
            ->where('games_played', '>=', 15)
            ->with([
                'player' => fn($q) => $q->select('id', 'name', 'country_code', 'race')
                                         ->whereNull('player_id'), // exclude aliases
            ])
            ->get()
            ->filter(function (PlayerRating $pr) use ($cutoff) {
                // Keep only players whose last game predates the cutoff
                $stat = PlayerStat::where('player_id', $pr->player_id)->first();
                return $stat && $stat->last_played_at < $cutoff;
            })
            ->sortByDesc('rating')
            ->values()
            ->map(function (PlayerRating $pr, int $index) use ($cutoff) {
                $stat = PlayerStat::where('player_id', $pr->player_id)->first();
                return (object) [
                    'rank'          => $index + 1,
                    'player'        => $pr->player,
                    'rating'        => (int) $pr->rating,
                    'games_played'  => $pr->games_played,
                    'wins'          => $pr->wins,
                    'losses'        => $pr->losses,
                    'last_played'   => $stat?->last_played_at,
                ];
            });
    }

    public function render()
    {
        return view('livewire.admin.inactive-ranking');
    }
}