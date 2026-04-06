<?php

namespace App\Livewire;

use App\Models\HeadToHead;
use App\Models\PlayerRating;
use App\Models\PlayerStat;
use App\Models\RatingHistory;
use App\Models\RatingSnapshot;
use App\Models\SystemStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $showMore = false;

    #[Computed]
    public function lastGameDate(): ?string
    {
        return SystemStat::get('last_game_date');
    }

    #[Computed]
    public function since(): ?Carbon
    {
        return $this->lastGameDate ? Carbon::parse($this->lastGameDate)->subYear() : null;
    }

    #[Computed]
    public function previousSnapshotDate(): ?string
    {
        return SystemStat::get('previous_snapshot_date');
    }

    #[Computed]
    public function top10()
    {
        if (!$this->since) return collect();

        // Single query — active players with 15+ games, no separate distinct query needed
        $ratings = PlayerRating::with('player')
            ->whereHas('player', fn($q) => $q->whereNull('player_id'))
            ->where('games_played', '>=', 15)
            ->whereHas('playerStat', fn($q) => $q->where('last_played_at', '>=', $this->since))
            ->orderByDesc('rating')
            ->limit(10)
            ->get();

        $playerIds = $ratings->pluck('player_id');
        $snapshots = RatingSnapshot::whereIn('player_id', $playerIds)
            ->where('snapshot_date', $this->previousSnapshotDate)
            ->get()
            ->keyBy('player_id');

        return $ratings->map(function ($row) use ($snapshots) {
            $row->prev_rating = $snapshots->get($row->player_id)?->rating;
            return $row;
        });
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
    public function spreadTrend()
    {
        // Single query: per snapshot_date compute top-15 avg and bottom-15 avg.
        // Bottom 15 = players whose rank is within 15 of the max rank for that date.
        // Snapshots only contain qualified players so no extra filtering needed.
        $rows = DB::table('rating_snapshots as s')
            ->joinSub(
                DB::table('rating_snapshots')
                    ->selectRaw('snapshot_date, MAX(`rank`) as max_rank')
                    ->groupBy('snapshot_date'),
                'mx',
                'mx.snapshot_date', '=', 's.snapshot_date'
            )
            ->selectRaw("
                s.snapshot_date,
                AVG(CASE WHEN s.rank <= 15 THEN s.rating END) as top_avg,
                AVG(CASE WHEN s.rank > mx.max_rank - 15 THEN s.rating END) as bot_avg
            ")
            ->groupBy('s.snapshot_date')
            ->orderBy('s.snapshot_date')
            ->get();

        return $rows->filter(fn($r) => $r->top_avg && $r->bot_avg)
            ->map(fn($r) => [
                'date'    => $r->snapshot_date,
                'top_avg' => round($r->top_avg),
                'bot_avg' => round($r->bot_avg),
                'spread'  => round($r->top_avg - $r->bot_avg),
            ]);
    }

    #[Computed]
    public function longestStreaks()
    {
        if (!$this->showMore || !$this->since) return collect();

        // Uses pre-computed player_stats instead of loading all history into PHP
        return DB::table('player_stats')
            ->join('players', 'players.id', '=', 'player_stats.player_id')
            ->where('player_stats.last_played_at', '>=', $this->since)
            ->where('player_stats.current_streak', '>', 0)
            ->orderByDesc('player_stats.current_streak')
            ->select('players.id', 'players.name', 'players.country_code', 'players.race', 'player_stats.current_streak as streak')
            ->limit(5)
            ->get();
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

        // Uses pre-computed head_to_head table instead of self-JOIN on rating_histories
        return HeadToHead::with('playerA', 'playerB')
            ->orderByDesc('games_count')
            ->limit(10)
            ->get()
            ->map(fn($h2h) => [
                'player_a_id'   => $h2h->player_a_id,
                'player_b_id'   => $h2h->player_b_id,
                'p1_name'       => $h2h->playerA->name,
                'p1_country'    => $h2h->playerA->country_code,
                'p1_race'       => $h2h->playerA->race,
                'p2_name'       => $h2h->playerB->name,
                'p2_country'    => $h2h->playerB->country_code,
                'p2_race'       => $h2h->playerB->race,
                'games_count'   => $h2h->games_count,
                'player_a_wins' => $h2h->player_a_wins,
                'player_b_wins' => $h2h->games_count - $h2h->player_a_wins,
            ]);
    }

    #[Computed]
    public function raceMatchups()
    {
        // Pre-computed in system_stats by StatsService
        return collect(SystemStat::get('race_matchups') ?? [])
            ->map(fn($row) => (object) $row);
    }

    #[Computed]
    public function gamesPerYear()
    {
        // Pre-computed in system_stats by StatsService
        return collect(SystemStat::get('games_per_year') ?? [])
            ->map(fn($row) => (object) $row);
    }

    #[Computed]
    public function activePlayersPerYear()
    {
        // Pre-computed in system_stats by StatsService
        return collect(SystemStat::get('active_players_per_year') ?? [])
            ->map(fn($row) => (object) $row);
    }

    #[Computed]
    public function upcomingEvents()
    {
        return \App\Models\Event::where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}