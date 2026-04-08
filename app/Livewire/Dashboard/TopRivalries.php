<?php

namespace App\Livewire\Dashboard;

use App\Models\HeadToHead;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TopRivalries extends Component
{
    #[Computed]
    public function rivalries()
    {
        return HeadToHead::with('playerA', 'playerB')
            ->orderByDesc('games_count')
            ->limit(10)
            ->get()
            ->map(fn($h2h) => [
                'player_a_id'   => $h2h->player_a_id,
                'player_b_id'   => $h2h->player_b_id,
                'p1_name'       => $h2h->playerA->name,
                'p1_country'    => $h2h->playerA->country_code,
                'p1_race'       => $h2h->playerA->race,
                'p2_name'       => $h2h->playerB->name,
                'p2_country'    => $h2h->playerB->country_code,
                'p2_race'       => $h2h->playerB->race,
                'games_count'   => $h2h->games_count,
                'player_a_wins' => $h2h->player_a_wins,
                'player_b_wins' => $h2h->games_count - $h2h->player_a_wins,
            ]);
    }

    public function render()
    {
        return view('livewire.dashboard.top-rivalries');
    }
}