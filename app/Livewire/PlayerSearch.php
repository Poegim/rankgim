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

        $playerIds = \App\Models\PlayerName::where('name', 'like', '%' . $this->query . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id')
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.player-search');
    }
}