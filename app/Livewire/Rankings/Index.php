<?php

namespace App\Livewire\Rankings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\PlayerRating;

class Index extends Component
{
    use WithPagination;

    public string $sortBy = 'rating';
    public string $sortDirection = 'desc';

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function rankings()
    {
        return PlayerRating::with('player')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.rankings.index');
    }
}
