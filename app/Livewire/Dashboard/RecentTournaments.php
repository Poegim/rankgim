<?php

namespace App\Livewire\Dashboard;

use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentTournaments extends Component
{
    #[Computed]
    public function tournaments()
    {
        // Sort tournaments by the date of their most recent game
        return Tournament::withCount('games')
            ->withMin('games', 'date_time')
            ->withMax('games', 'date_time')
            ->having('games_count', '>', 0)
            ->orderByDesc('games_max_date_time')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.recent-tournaments');
    }
}