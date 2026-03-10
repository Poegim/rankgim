<?php

namespace App\Livewire\Games;

use App\Models\Game;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $filterTournament = '';

    #[Computed]
    public function games()
    {
        return Game::with(['winner', 'loser', 'tournament'])
            ->when($this->dateFrom, fn($q) => $q->whereDate('date_time', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn($q) => $q->whereDate('date_time', '<=', $this->dateTo))
            ->when($this->filterTournament, fn($q) => $q->whereHas('tournament', fn($q) => $q->where('name', 'like', '%' . $this->filterTournament . '%')))
            ->orderByDesc('date_time')
            ->paginate(20);
    }

    public function updatedFilterTournament() { $this->resetPage(); }
    public function updatedDateFrom() { $this->resetPage(); }
    public function updatedDateTo()   { $this->resetPage(); }

    public function render()
    {
        return view('livewire.games.index');
    }
}