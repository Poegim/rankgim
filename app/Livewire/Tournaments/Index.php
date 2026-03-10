<?php

namespace App\Livewire\Tournaments;

use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    public bool $showModal = false;
    public bool $showEditModal = false;
    public string $name = '';
    public ?int $editId = null;
    public string $editName = '';

    public function updatedSearch() { $this->resetPage(); }

    #[Computed]
    public function tournaments()
    {
        return DB::table('tournaments')
            ->leftJoin('games', 'games.tournament_id', '=', 'tournaments.id')
            ->selectRaw('
                tournaments.id,
                tournaments.name,
                tournaments.created_at,
                count(games.id) as games_count,
                min(games.date_time) as first_game,
                max(games.date_time) as last_game
            ')
            ->when($this->search, fn($q) => $q->where('tournaments.name', 'like', '%' . $this->search . '%'))
            ->groupBy('tournaments.id', 'tournaments.name', 'tournaments.created_at')
            ->orderByDesc(DB::raw('COALESCE(MAX(games.date_time), tournaments.created_at)'))
            ->paginate(20);
    }

    public function openModal(): void
    {
        $this->showModal = true;
        $this->name = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->name = '';
    }

    public function edit(int $id): void
    {
        $tournament = Tournament::findOrFail($id);
        $this->editId = $id;
        $this->editName = $tournament->name;
        $this->showEditModal = true;
    }

    public function update(): void
    {
        $this->validate(['editName' => 'required|min:2|max:255']);
        Tournament::findOrFail($this->editId)->update(['name' => $this->editName]);
        $this->showEditModal = false;
        unset($this->tournaments);
        $this->dispatch('tournament-saved');
    }

    public function delete(int $id): void
    {
        $tournament = Tournament::findOrFail($id);
        
        if ($tournament->games()->count() > 0) {
            $this->dispatch('cannot-delete');
            return;
        }
        
        $tournament->delete();
        unset($this->tournaments);
        $this->dispatch('tournament-saved');
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|min:2|max:255']);
        
        Tournament::create(['name' => $this->name]);
        
        $this->closeModal();
        unset($this->tournaments);
        $this->dispatch('tournament-saved');
    }

    public function render()
    {
        return view('livewire.tournaments.index');
    }
}