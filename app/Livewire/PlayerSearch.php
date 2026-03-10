<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Player;

class PlayerSearch extends Component
{
    public string $query = '';

    #[Computed]
    public function results()
    {
        if (strlen($this->query) < 2) return collect();

        return Player::where('players.name', 'like', '%' . $this->query . '%')
            ->whereNull('players.player_id')
            ->whereHas('rating')
            ->with('rating')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->orderByDesc('player_ratings.rating')
            ->select('players.*')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.player-search');
    }
}