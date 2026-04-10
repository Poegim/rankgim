<?php

namespace App\Livewire\Dashboard;

use App\Models\Tournament;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentTournaments extends Component
{
    // RecentTournaments.php
    #[Computed]
    public function tournaments()
    {
        return Cache::remember('dashboard.recent_tournaments', 3600, function () {
            return Tournament::withCount('games')
                ->withMin('games', 'date_time')
                ->withMax('games', 'date_time')
                ->having('games_count', '>', 0)
                ->orderByDesc('games_max_date_time')
                ->limit(5)
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.dashboard.recent-tournaments');
    }
}