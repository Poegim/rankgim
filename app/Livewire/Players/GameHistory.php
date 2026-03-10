<?php

namespace App\Livewire\Players;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\RatingHistory;

class GameHistory extends Component
{
    use WithPagination;

    public int $playerId;

    #[Computed]
    public function games()
    {
        return RatingHistory::where('player_id', $this->playerId)
            ->with('game.winner', 'game.loser')
            ->orderBy('played_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.players.game-history');
    }
}