<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Player;
use App\Models\PlayerRating;
use App\Models\RatingHistory;
use App\Models\RatingSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class Dashboard extends Component
{
    public bool $showMore = false;

    #[Computed]
    public function lastGameDate(): ?string
    {
        return RatingHistory::max('played_at');
    }

    #[Computed]
    public function since(): ?Carbon
    {
        return $this->lastGameDate ? Carbon::parse($this->lastGameDate)->subYear() : null;
    }

    #[Computed]
    public function previousSnapshotDate(): ?string
    {
        $latestDate = RatingSnapshot::max('snapshot_date');
        if (!$latestDate) return null;
        return RatingSnapshot::where('snapshot_date', '<', $latestDate)->max('snapshot_date');
    }

    #[Computed]
    public function top10()
    {
        if (!$this->since) return collect();

        $activePlayerIds = RatingHistory::where('played_at', '>=', $this->since)
            ->distinct()->pluck('player_id');

        $ratings = PlayerRating::with('player')
            ->whereIn('player_id', $activePlayerIds)
            ->where('games_played', '>=', 15)
            ->orderByDesc('rating')
            ->limit(10)
            ->get();

        $playerIds = $ratings->pluck('player_id');
        $snapshots = \App\Models\RatingSnapshot::whereIn('player_id', $playerIds)
            ->where('snapshot_date', $this->previousSnapshotDate)
            ->get()
            ->keyBy('player_id');

        return $ratings->map(function ($row) use ($snapshots) {
            $row->prev_rating = $snapshots->get($row->player_id)?->rating;
            return $row;
        });
    }

    #[Computed]
    public function highestPeaks()
    {
        return RatingHistory::selectRaw('player_id, MAX(rating_after) as peak_rating')
            ->groupBy('player_id')
            ->orderByDesc('peak_rating')
            ->with('player')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function recentGames()
    {
        return RatingHistory::with('game.winner', 'game.loser')
            ->where('result', 'win')
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function biggestRisers()
    {
        if (!$this->showMore || !$this->previousSnapshotDate) return collect();

        return DB::table('player_ratings')
            ->join('players', 'players.id', '=', 'player_ratings.player_id')
            ->join('rating_snapshots', function ($join) {
                $join->on('rating_snapshots.player_id', '=', 'player_ratings.player_id')
                     ->where('rating_snapshots.snapshot_date', $this->previousSnapshotDate);
            })
            ->selectRaw('players.id, players.name, players.country_code, players.country, players.race, player_ratings.rating, (player_ratings.rating - rating_snapshots.rating) as rating_change')
            ->orderByRaw('rating_change DESC')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function biggestFallers()
    {
        if (!$this->showMore || !$this->previousSnapshotDate) return collect();

        return DB::table('player_ratings')
            ->join('players', 'players.id', '=', 'player_ratings.player_id')
            ->join('rating_snapshots', function ($join) {
                $join->on('rating_snapshots.player_id', '=', 'player_ratings.player_id')
                     ->where('rating_snapshots.snapshot_date', $this->previousSnapshotDate);
            })
            ->selectRaw('players.id, players.name, players.country_code, players.country, players.race, player_ratings.rating, (player_ratings.rating - rating_snapshots.rating) as rating_change')
            ->orderByRaw('rating_change ASC')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function longestStreaks()
    {
        if (!$this->showMore || !$this->since) return collect();

        $activePlayers = RatingHistory::where('played_at', '>=', $this->since)
            ->pluck('player_id')
            ->unique();

        $results = RatingHistory::orderBy('player_id')
            ->orderByDesc('played_at')
            ->whereIn('player_id', $activePlayers)
            ->get(['player_id', 'result'])
            ->groupBy('player_id')
            ->map(function ($entries) {
                $streak = 0;
                foreach ($entries as $entry) {
                    if ($entry->result === 'win') $streak++;
                    else break;
                }
                return $streak;
            })
            ->sortByDesc(fn($streak) => $streak)
            ->take(5);

        $players = Player::whereIn('id', $results->keys())->get()->keyBy('id');

        return $results->map(fn($streak, $playerId) => [
            'player' => $players[$playerId],
            'streak' => $streak,
        ])->values();
    }

    #[Computed]
    public function mostActives()
    {
        if (!$this->showMore || !$this->since) return collect();

        return RatingHistory::where('played_at', '>=', $this->since)
            ->selectRaw('player_id, count(*) as games_count')
            ->groupBy('player_id')
            ->orderByDesc('games_count')
            ->with('player')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function biggestUpsets()
    {
        if (!$this->showMore || !$this->since) return collect();

        return RatingHistory::where('result', 'win')
            ->where('played_at', '>=', $this->since)
            ->with('game.winner', 'game.loser')
            ->whereHas('game.winner', fn($q) => $q->whereHas('rating', fn($q) => $q->where('games_played', '>=', 15)))
            ->whereHas('game.loser', fn($q) => $q->whereHas('rating', fn($q) => $q->where('games_played', '>=', 15)))
            ->orderByRaw('rating_before - (SELECT rating_before FROM rating_histories rh2 WHERE rh2.game_id = rating_histories.game_id AND rh2.result = "loss") ASC')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function mostDominant()
    {
        if (!$this->showMore || !$this->since) return collect();

        return RatingHistory::where('played_at', '>=', $this->since)
            ->selectRaw('player_id, count(*) as total, sum(result = "win") as wins')
            ->groupBy('player_id')
            ->having('total', '>=', 15)
            ->orderByRaw('wins / total DESC')
            ->with('player')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function topRivalries()
    {
        if (!$this->showMore) return collect();

        $rows = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->selectRaw('
                LEAST(rh1.player_id, rh2.player_id) as player_a_id,
                GREATEST(rh1.player_id, rh2.player_id) as player_b_id,
                count(*) as games_count,
                sum(CASE WHEN rh1.player_id = LEAST(rh1.player_id, rh2.player_id) THEN 1 ELSE 0 END) as player_a_wins,
                MAX(CASE WHEN rh1.player_id = LEAST(rh1.player_id, rh2.player_id) THEN p1.name ELSE p2.name END) as p1_name,
                MAX(CASE WHEN rh1.player_id = LEAST(rh1.player_id, rh2.player_id) THEN p1.country_code ELSE p2.country_code END) as p1_country,
                MAX(CASE WHEN rh1.player_id = LEAST(rh1.player_id, rh2.player_id) THEN p1.race ELSE p2.race END) as p1_race,
                MAX(CASE WHEN rh1.player_id = GREATEST(rh1.player_id, rh2.player_id) THEN p1.name ELSE p2.name END) as p2_name,
                MAX(CASE WHEN rh1.player_id = GREATEST(rh1.player_id, rh2.player_id) THEN p1.country_code ELSE p2.country_code END) as p2_country,
                MAX(CASE WHEN rh1.player_id = GREATEST(rh1.player_id, rh2.player_id) THEN p1.race ELSE p2.race END) as p2_race
            ')
            ->groupByRaw('LEAST(rh1.player_id, rh2.player_id), GREATEST(rh1.player_id, rh2.player_id)')
            ->orderByDesc('games_count')
            ->limit(10)
            ->get();

        return $rows->map(fn($row) => [
            'player_a_id'   => $row->player_a_id,
            'player_b_id'   => $row->player_b_id,
            'p1_name'       => $row->p1_name,
            'p1_country'    => $row->p1_country,
            'p1_race'       => $row->p1_race,
            'p2_name'       => $row->p2_name,
            'p2_country'    => $row->p2_country,
            'p2_race'       => $row->p2_race,
            'games_count'   => $row->games_count,
            'player_a_wins' => $row->player_a_wins,
            'player_b_wins' => $row->games_count - $row->player_a_wins,
        ])->values();
    }

    #[Computed]
    public function raceMatchups()
    {
        return DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->whereNotIn('p1.race', ['Random', 'Unknown'])
            ->whereNotIn('p2.race', ['Random', 'Unknown'])
            ->selectRaw('p1.race as winner_race, p2.race as loser_race, count(*) as games')
            ->groupBy('p1.race', 'p2.race')
            ->get();
    }

    #[Computed]
    public function qualifiedCountries()
    {
        if (!$this->showMore || !$this->since) return collect();

        return DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->join('rating_histories', 'rating_histories.player_id', '=', 'players.id')
            ->where('player_ratings.games_played', '>=', 15)
            ->where('rating_histories.played_at', '>=', $this->since)
            ->whereNotIn('players.country_code', ['XX'])
            ->selectRaw('players.country, players.country_code, count(distinct players.id) as player_count')
            ->groupBy('players.country', 'players.country_code')
            ->having('player_count', '>=', 5)
            ->orderByDesc('player_count')
            ->limit(10)
            ->pluck('country_code')
            ->toArray();
    }

    #[Computed]
    public function topCountries()
    {
        if (!$this->showMore || !$this->since || empty($this->qualifiedCountries)) return collect();

        $activePlayerIds = DB::table('rating_histories')
            ->where('played_at', '>=', $this->since)
            ->distinct()
            ->pluck('player_id');

        return DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->where('player_ratings.games_played', '>=', 15)
            ->whereIn('players.country_code', $this->qualifiedCountries)
            ->whereIn('players.id', $activePlayerIds)
            ->selectRaw('
                players.country,
                players.country_code,
                count(distinct players.id) as player_count,
                round(avg(player_ratings.rating)) as avg_rating,
                sum(player_ratings.wins) as total_wins,
                sum(player_ratings.losses) as total_losses,
                round(sum(player_ratings.wins) / (sum(player_ratings.wins) + sum(player_ratings.losses)) * 100) as win_ratio
            ')
            ->groupBy('players.country', 'players.country_code')
            ->orderByDesc('avg_rating')
            ->get();
    }

    #[Computed]
    public function countryMatchups()
    {
        if (!$this->showMore || !$this->since || empty($this->qualifiedCountries)) return collect();

        return DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->whereIn('p1.country_code', $this->qualifiedCountries)
            ->whereIn('p2.country_code', $this->qualifiedCountries)
            ->where('p1.country_code', '!=', 'p2.country_code')
            ->where('rh1.played_at', '>=', $this->since)
            ->selectRaw('p1.country_code as winner_country, p2.country_code as loser_country, count(*) as games')
            ->groupBy('p1.country_code', 'p2.country_code')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}