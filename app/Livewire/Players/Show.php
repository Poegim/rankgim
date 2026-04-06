<?php

namespace App\Livewire\Players;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Player;
use App\Models\PlayerRating;
use App\Models\RatingHistory;
use Livewire\WithPagination;
use App\Models\RatingSnapshot;

class Show extends Component
{
    use WithPagination;

    public int $playerId;

    #[Computed]
    public function player()
    {
        return Player::findOrFail($this->playerId);
    }

    #[Computed]
    public function rating()
    {
        return PlayerRating::where('player_id', $this->playerId)->first();
    }

    #[Computed]
    public function history()
    {
        return RatingHistory::where('player_id', $this->playerId)
            ->orderBy('played_at')
            ->orderBy('id')
            ->get(['played_at', 'rating_after', 'rating_change', 'result']);
    }

    #[Computed]
    public function games()
    {
        return RatingHistory::where('player_id', $this->playerId)
            ->with('game.winner', 'game.loser')
            ->orderBy('played_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        $rating = $this->rating;
        $ps     = \App\Models\PlayerStat::where('player_id', $this->playerId)->first();

        $lastGame = \App\Models\RatingHistory::max('played_at');
        $since    = $lastGame ? \Carbon\Carbon::parse($lastGame)->subMonths(config('rankgim.inactive_months')) : null;

        // Determine player status for the profile banner
        $tooFewGames  = !$rating || $rating->games_played < 15;
        $isInactive   = $since && $ps && $ps->last_played_at < $since->toDateString();
        $lastPlayedAt = $ps?->last_played_at;

        // Calculate current live rank — only if the player qualifies for the ranking
        $currentRank = null;
        if (!$tooFewGames && !$isInactive) {
            $currentRank = \App\Models\PlayerRating::where('rating', '>', $rating->rating)
                ->where('games_played', '>=', 15)
                ->when($since, fn($q) => $q->whereHas(
                    'playerStat',
                    fn($q2) => $q2->where('last_played_at', '>=', $since)
                ))
                ->count() + 1;
        }

        return [
            'win_ratio'          => $rating && $rating->games_played > 0
                                        ? round(($rating->wins / $rating->games_played) * 100)
                                        : 0,
            'peak_rating'        => $ps?->peak_rating ?? 0,
            'longest_win_streak' => $ps?->longest_win_streak ?? 0,
            'current_streak'     => $ps?->current_streak ?? 0,
            'best_rank'          => $ps?->best_rank ?? '—',
            'current_rank'       => $currentRank,
            'too_few_games'      => $tooFewGames,
            'is_inactive'        => $isInactive,
            'last_played_at'     => $lastPlayedAt,
            'games_played'       => $rating?->games_played ?? 0,
        ];
    }


    #[Computed]
    public function raceStats()
    {
        $results = RatingHistory::where('player_id', $this->playerId)
            ->with('game.winner', 'game.loser')
            ->get()
            ->groupBy(function ($entry) {
                $opponent = $entry->result === 'win' ? $entry->game->loser : $entry->game->winner;
                return $opponent?->race ?? 'Unknown';
            })
            ->filter(fn($entries, $race) => !in_array($race, ['Unknown', 'Random']))
            ->map(function ($entries, $race) {
                $wins = $entries->where('result', 'win')->count();
                $total = $entries->count();
                return [
                    'race'   => $race,
                    'wins'   => $wins,
                    'losses' => $total - $wins,
                    'total'  => $total,
                    'ratio'  => $total > 0 ? round(($wins / $total) * 100) : 0,
                ];
            })
            ->sortBy('race')
            ->values();

        return $results;
    }
    
    #[Computed]
    public function rankHistory()
    {
        $snapshots = RatingSnapshot::where('player_id', $this->playerId)
            ->orderBy('snapshot_date')
            ->get(['snapshot_date', 'rank', 'rating']);

        if ($snapshots->count() < 2) return $snapshots;

        $result = collect();
        foreach ($snapshots as $i => $current) {
            if ($i === 0) {
                $result->push($current);
                continue;
            }

            $prev = $snapshots[$i - 1];
            $monthsGap = \Carbon\Carbon::parse($prev->snapshot_date)
                ->diffInMonths($current->snapshot_date);

            // Insert a null-y point with a valid date to break the line on long gaps
            if ($monthsGap > 3) {
                $midDate = \Carbon\Carbon::parse($prev->snapshot_date)
                    ->addMonths((int) ($monthsGap / 2))
                    ->endOfMonth()
                    ->toDateString();

                $result->push((object)[
                    'snapshot_date' => $midDate,
                    'rank'          => null,
                    'rating'        => null,
                ]);
            }

            $result->push($current);
        }

        return $result;
    }

    #[Computed]
    public function headToHead()
    {
        return RatingHistory::where('player_id', $this->playerId)
            ->with('game.winner', 'game.loser')
            ->get()
            ->groupBy(function ($entry) {
                $opponent = $entry->result === 'win' ? $entry->game->loser : $entry->game->winner;
                return $opponent?->id;
            })
            ->filter(fn($entries, $opponentId) => $opponentId !== null)
            ->map(function ($entries, $opponentId) {
                $opponent = $entries->first()->result === 'win'
                    ? $entries->first()->game->loser
                    : $entries->first()->game->winner;

                $wins   = $entries->where('result', 'win')->count();
                $losses = $entries->where('result', 'loss')->count();
                $total  = $entries->count();

                return [
                    'opponent'  => $opponent,
                    'wins'      => $wins,
                    'losses'    => $losses,
                    'total'     => $total,
                    'ratio'     => $total > 0 ? round(($wins / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();
    }

    public function render()
    {
        return view('livewire.players.show');
    }
}