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
            ->get(['played_at', 'rating_after', 'rating_change', 'result']);
    }

    #[Computed]
    public function games()
    {
        return RatingHistory::where('player_id', $this->playerId)
            ->with('game.winner', 'game.loser')
            ->orderBy('played_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function stats()
    {
        $history = $this->history;
        $rating = $this->rating;
        
        return [
            'win_ratio'          => $rating && $rating->games_played > 0
                                        ? round(($rating->wins / $rating->games_played) * 100)
                                        : 0,
            'peak_rating'        => $history->max('rating_after') ?? 0,
            'longest_win_streak' => $this->calculateWinStreak(),
            'current_streak'     => $this->calculateCurrentStreak(),
            'best_rank'          => RatingSnapshot::where('player_id', $this->playerId)->min('rank') ?? '—',
        ];
    }

    private function calculateWinStreak(): int
    {
        $streak = 0;
        $max = 0;

        foreach ($this->history as $entry) {
            if ($entry->result === 'win') {
                $streak++;
                $max = max($max, $streak);
            } else {
                $streak = 0;
            }
        }

        return $max;
    }

    private function calculateCurrentStreak(): int
    {
        $streak = 0;
        $lastResult = null;

        foreach ($this->history->reverse() as $entry) {
            if ($lastResult === null) {
                $lastResult = $entry->result;
            }
            if ($entry->result === $lastResult) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak * ($lastResult === 'win' ? 1 : -1);
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