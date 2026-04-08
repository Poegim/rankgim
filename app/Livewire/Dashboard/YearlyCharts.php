<?php

namespace App\Livewire\Dashboard;

use App\Models\SystemStat;
use Livewire\Attributes\Computed;
use Livewire\Component;

class YearlyCharts extends Component
{
    #[Computed]
    public function gamesPerYear()
    {
        return collect(SystemStat::get('games_per_year') ?? [])
            ->map(fn($row) => (object) $row);
    }

    #[Computed]
    public function activePlayersPerYear()
    {
        return collect(SystemStat::get('active_players_per_year') ?? [])
            ->map(fn($row) => (object) $row);
    }

    public function render()
    {
        return view('livewire.dashboard.yearly-charts');
    }
}