<?php

namespace App\Livewire\Rankings;

use App\Models\PlayerRating;
use App\Models\RatingHistory;
use App\Models\RatingSnapshot;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    #[Url]
    public ?string $filterRegion = null;

    public function filterByRegion(string $region): void
    {
        $this->filterRegion = $this->filterRegion === $region ? null : $region;
        $this->resetPage();
    }

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
        $lastGame = RatingHistory::max('played_at');
        if (!$lastGame) return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        $since = Carbon::parse($lastGame)->subYear();

        $activePlayerIds = RatingHistory::where('played_at', '>=', $since)
            ->distinct()->pluck('player_id');

        $rankings = PlayerRating::with('player')
            ->whereIn('player_id', $activePlayerIds)
            ->where('games_played', '>=', 15)
            ->when($this->filterCountryCode, fn($q) => $q->whereHas('player', fn($q) => $q->where('country_code', $this->filterCountryCode)))
            ->when($this->filterRace, fn($q) => $q->whereHas('player', fn($q) => $q->where('race', $this->filterRace)))
            ->when($this->filterRegion, function ($q) {
                    $codes = collect(config('countries'))->where('region', $this->filterRegion)->pluck('code')->toArray();
                    $q->whereHas('player', fn($q) => $q->whereIn('country_code', $codes));
                })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(100);

        $playerIds = $rankings->pluck('player_id');
        $prevDate = RatingSnapshot::max('snapshot_date')
            ? RatingSnapshot::where('snapshot_date', '<', RatingSnapshot::max('snapshot_date'))->max('snapshot_date')
            : null;

        $snapshots = $prevDate
            ? RatingSnapshot::whereIn('player_id', $playerIds)->where('snapshot_date', $prevDate)->get()->keyBy('player_id')
            : collect();

        $rankings->getCollection()->transform(function ($row) use ($snapshots) {
            $row->prev_rating = $snapshots->get($row->player_id)?->rating;
            return $row;
        });

        return $rankings;
    }

    public function render()
    {
        return view('livewire.rankings.index');
    }
}