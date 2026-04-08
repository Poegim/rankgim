<?php

namespace App\Livewire\Dashboard;

use App\Models\SystemStat;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RaceMatchups extends Component
{
    #[Computed]
    public function matchups()
    {
        return collect(SystemStat::get('race_matchups') ?? [])
            ->map(fn($row) => (object) $row)
            ->keyBy(fn($r) => $r->winner_race . '-' . $r->loser_race);
    }

    public function render()
    {
        return view('livewire.dashboard.race-matchups');
    }
}