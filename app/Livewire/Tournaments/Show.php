<?php

namespace App\Livewire\Tournaments;

use App\Models\Game;
use App\Models\Tournament;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public int $tournamentId;

    #[Computed]
    public function tournament()
    {
        return Tournament::findOrFail($this->tournamentId);
    }

    #[Computed]
    public function games()
    {
        return Game::with(['winner', 'loser'])
            ->where('tournament_id', $this->tournamentId)
            ->orderByDesc('date_time')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.tournaments.show');
    }
}