<?php

namespace App\Livewire\Rankings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Models\PlayerRating;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $sortBy = 'rating';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public ?string $filterCountryCode = null;

    #[Url]
    public ?string $filterRace = null;

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function filterByCountry(string $countryCode): void
    {
        $this->filterCountryCode = $this->filterCountryCode === $countryCode ? null : $countryCode;
        $this->resetPage();
    }

    public function filterByRace(string $race): void
    {
        $this->filterRace = $this->filterRace === $race ? null : $race;
        $this->resetPage();
    }

    #[Computed]
    public function rankings()
    {
        $lastGameDate = \App\Models\RatingHistory::max('played_at');
        $since = \Carbon\Carbon::parse($lastGameDate)->subYear();

        return PlayerRating::with('player')
            ->where('games_played', '>=', 15)
            ->whereHas('player.ratingHistory', fn($q) => $q->where('played_at', '>=', $since))
            ->whereHas('player', function ($query) {
                if ($this->filterCountryCode) {
                    $query->where('country_code', $this->filterCountryCode);
                }
                if ($this->filterRace) {
                    $query->where('race', $this->filterRace);
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.rankings.index');
    }
}