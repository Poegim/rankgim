<?php

namespace App\Livewire\Dashboard;

use App\Models\RatingHistory;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentGames extends Component
{
    #[Computed]
    public function games()
    {
        return RatingHistory::with('game.winner', 'game.loser')
            ->where('result', 'win')
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.recent-games');
    }
}