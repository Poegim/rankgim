<?php

namespace App\Livewire\Tournaments;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch() { $this->resetPage(); }

    #[Computed]
    public function tournaments()
    {
        return DB::table('tournaments')
            ->leftJoin('games', 'games.tournament_id', '=', 'tournaments.id')
            ->selectRaw('
                tournaments.id,
                tournaments.name,
                count(games.id) as games_count,
                min(games.date_time) as first_game,
                max(games.date_time) as last_game
            ')
            ->when($this->search, fn($q) => $q->where('tournaments.name', 'like', '%' . $this->search . '%'))
            ->groupBy('tournaments.id', 'tournaments.name')
            ->orderByDesc('last_game')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.tournaments.index');
    }
}