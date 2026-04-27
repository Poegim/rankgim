<?php

namespace App\Livewire\Admin;

use App\Models\PlayerRating;
use App\Models\RatingHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class InactiveRanking extends Component
{
    use WithPagination;

    // Allowed sortable columns mapped to their default sort direction
    private const SORTABLE_COLUMNS = [
        'rating'       => 'desc',
        'games_played' => 'desc',
        'wins'         => 'desc',
        'losses'       => 'desc',
        'last_played'  => 'asc',
    ];

    private const PER_PAGE = 50;

    public string $sortBy  = 'rating';
    public string $sortDir = 'desc';

    // ── Sorting ───────────────────────────────────────────────────────────────

    /**
     * Toggle sort column. Clicking the active column flips direction;
     * clicking a new column resets to that column's default direction.
     */
    public function sort(string $column): void
    {
        if (! array_key_exists($column, self::SORTABLE_COLUMNS)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $column;
            $this->sortDir = self::SORTABLE_COLUMNS[$column];
        }

        $this->resetPage();
    }

    // ── Data ──────────────────────────────────────────────────────────────────

    /**
     * All inactive players as a flat collection (unsorted, unsliced).
     * Cached for the request so sorting and pagination share one DB round-trip.
     *
     * Inactive = 15+ games AND last_played_at < cutoff.
     * Cutoff is relative to the most recent game in the DB — same logic as
     * Rankings\Index and Players\Show.
     */
    #[Computed]
    public function allInactivePlayers(): Collection
    {
        $lastGame = RatingHistory::max('played_at');

        if (! $lastGame) {
            return collect();
        }

        $cutoff = Carbon::parse($lastGame)
            ->subMonths(config('rankgim.inactive_months'))
            ->toDateString();

        // Single query with joins — avoids N+1 from separate PlayerStat lookups
        return PlayerRating::query()
            ->where('player_ratings.games_played', '>=', 15)
            ->join('player_stats', 'player_stats.player_id', '=', 'player_ratings.player_id')
            ->join('players', 'players.id', '=', 'player_ratings.player_id')
            ->whereNull('players.player_id')                       // exclude aliases
            ->where('player_stats.last_played_at', '<', $cutoff)  // inactive only
            ->select([
                'players.id       as player_db_id',
                'players.name',
                'players.country_code',
                'players.race',
                'player_ratings.rating',
                'player_ratings.games_played',
                'player_ratings.wins',
                'player_ratings.losses',
                'player_stats.last_played_at as last_played',
            ])
            ->get();
    }

    /** Total count — shown in the header badge. */
    #[Computed]
    public function totalCount(): int
    {
        return $this->allInactivePlayers->count();
    }

    /**
     * Sorted and paginated slice of inactive players.
     * Rank number is assigned post-sort so it always matches the chosen order.
     */
    #[Computed]
    public function players(): Collection
    {
        $sorted = $this->sortDir === 'asc'
            ? $this->allInactivePlayers->sortBy($this->sortBy, SORT_REGULAR)
            : $this->allInactivePlayers->sortByDesc($this->sortBy, SORT_REGULAR);

        $page   = $this->getPage();
        $offset = ($page - 1) * self::PER_PAGE;

        return $sorted
            ->values()
            ->slice($offset, self::PER_PAGE)
            ->values()
            ->map(fn($row, int $i) => (object) [
                'rank'         => $offset + $i + 1,
                'player_id'    => $row->player_db_id,
                'name'         => $row->name,
                'country_code' => $row->country_code,
                'race'         => $row->race,
                'rating'       => (int) $row->rating,
                'games_played' => $row->games_played,
                'wins'         => $row->wins,
                'losses'       => $row->losses,
                'last_played'  => $row->last_played,
            ]);
    }

    /** Total pages — used by pagination controls in the view. */
    #[Computed]
    public function totalPages(): int
    {
        return (int) ceil($this->totalCount / self::PER_PAGE);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return the sort arrow character for a column header.
     * Inactive columns show a neutral ↕ indicator.
     */
    public function sortIcon(string $column): string
    {
        if ($this->sortBy !== $column) {
            return '↕';
        }

        return $this->sortDir === 'asc' ? '↑' : '↓';
    }

    public function render()
    {
        return view('livewire.admin.inactive-ranking');
    }
}