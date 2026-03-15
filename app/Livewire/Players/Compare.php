<?php

namespace App\Livewire\Players;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Player;
use App\Models\PlayerRating;
use App\Models\RatingHistory;

class Compare extends Component
{
    public int $id1;
    public int $id2;

    #[Computed]
    public function player1()
    {
        return Player::findOrFail($this->id1);
    }

    #[Computed]
    public function player2()
    {
        return Player::findOrFail($this->id2);
    }

    #[Computed]
    public function rating1()
    {
        return PlayerRating::where('player_id', $this->id1)->first();
    }

    #[Computed]
    public function rating2()
    {
        return PlayerRating::where('player_id', $this->id2)->first();
    }

    #[Computed]
    public function h2h()
    {
        $games = RatingHistory::where('player_id', $this->id1)
            ->whereHas('game', function ($q) {
                $q->where(function ($q) {
                    $q->where('winner_id', $this->id1)->where('loser_id', $this->id2);
                })->orWhere(function ($q) {
                    $q->where('winner_id', $this->id2)->where('loser_id', $this->id1);
                });
            })
            ->with('game.winner', 'game.loser')
            ->get();

        $p1wins  = $games->where('result', 'win')->count();
        $p1loss  = $games->where('result', 'loss')->count();
        $total   = $games->count();

        return [
            'p1wins'  => $p1wins,
            'p2wins'  => $p1loss,
            'total'   => $total,
            'p1ratio' => $total > 0 ? round(($p1wins / $total) * 100) : 50,
            'p2ratio' => $total > 0 ? round(($p1loss / $total) * 100) : 50,
        ];
    }

    #[Computed]
    public function recentH2hGames()
    {
        return RatingHistory::where('player_id', $this->id1)
            ->whereHas('game', function ($q) {
                $q->where(function ($q) {
                    $q->where('winner_id', $this->id1)->where('loser_id', $this->id2);
                })->orWhere(function ($q) {
                    $q->where('winner_id', $this->id2)->where('loser_id', $this->id1);
                });
            })
            ->with('game.winner', 'game.loser', 'game.tournament')
            ->orderByDesc('played_at')
            ->orderByDesc('game_id')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function ratingHistory1()
    {
        return RatingHistory::where('player_id', $this->id1)
            ->orderBy('played_at')
            ->orderBy('id')
            ->get(['played_at', 'rating_after'])
            ->map(fn($r) => [
                'x' => \Carbon\Carbon::parse($r->played_at)->format('Y-m-d'),
                'y' => $r->rating_after,
            ]);
    }

    #[Computed]
    public function ratingHistory2()
    {
        return RatingHistory::where('player_id', $this->id2)
            ->orderBy('played_at')
            ->orderBy('id')
            ->get(['played_at', 'rating_after'])
            ->map(fn($r) => [
                'x' => \Carbon\Carbon::parse($r->played_at)->format('Y-m-d'),
                'y' => $r->rating_after,
            ]);
    }

    public function render()
    {
        return view('livewire.players.compare');
    }
}