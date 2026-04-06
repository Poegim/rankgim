<?php

namespace App\Services;

use App\Models\Player;
use App\Models\PlayerRating;
use App\Models\RatingHistory;
use App\Models\RatingSnapshot;
use App\Models\SystemStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    /**
     * Rebuild all pre-computed stats tables.
     * Called automatically after EloService::recalculateAll().
     *
     * @param array $stats  In-memory stats from EloService: [player_id => [games_played, wins, losses, draws, last_played_at]]
     * @param array $ratings In-memory ratings from EloService: [player_id => rating]
     */
    public function rebuild(array $stats, array $ratings): void
    {
        echo "Building player_stats...\n";
        $this->buildPlayerStats($stats, $ratings);

        echo "Building country_stats...\n";
        $this->buildCountryStats();

        echo "Building head_to_head...\n";
        $this->buildHeadToHead();

        echo "Building country_matchups...\n";
        $this->buildCountryMatchups();

        echo "Building system_stats...\n";
        $this->buildSystemStats();

        echo "Flushing cache...\n";
        Cache::flush();

        echo "StatsService done!\n";
    }

    private function buildCountryMatchups(): void
    {
        DB::table('country_matchups')->truncate();

        $codes = DB::table('country_stats')->pluck('country_code')->toArray();
        if (empty($codes)) return;

        $lastGame = DB::table('rating_histories')->max('played_at');
        if (!$lastGame) return;
        $since = Carbon::parse($lastGame)->subMonths(config('rankgim.inactive_months'));

        $rows = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->whereIn('p1.country_code', $codes)
            ->whereIn('p2.country_code', $codes)
            ->where('p1.country_code', '!=', 'p2.country_code')
            ->where('rh1.played_at', '>=', $since)
            ->selectRaw('p1.country_code as winner_country, p2.country_code as loser_country, count(*) as games')
            ->groupBy('p1.country_code', 'p2.country_code')
            ->get();

        $now = now();
        $batch = $rows->map(fn($row) => [
            'winner_country' => $row->winner_country,
            'loser_country'  => $row->loser_country,
            'games'          => $row->games,
            'created_at'     => $now,
            'updated_at'     => $now,
        ])->toArray();

        if (!empty($batch)) {
            DB::table('country_matchups')->insert($batch);
        }

        echo "country_matchups: " . count($batch) . " rows inserted.\n";
    }

    private function buildSystemStats(): void
    {
        // last_game_date — replaces RatingHistory::max('played_at') on every page load
        $lastGameDate = DB::table('rating_histories')->max('played_at');
        SystemStat::set('last_game_date', $lastGameDate);

        // previous_snapshot_date — replaces two slow MAX() queries on rating_snapshots
        $latestSnapshot = DB::table('rating_snapshots')->max('snapshot_date');
        $previousSnapshot = $latestSnapshot
            ? DB::table('rating_snapshots')
                ->where('snapshot_date', '<', $latestSnapshot)
                ->max('snapshot_date')
            : null;
        SystemStat::set('previous_snapshot_date', $previousSnapshot);

        // race_matchups — replaces self-JOIN on rating_histories
        $raceMatchups = DB::table('rating_histories as rh1')
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
            ->get()
            ->toArray();
        SystemStat::set('race_matchups', $raceMatchups);

        // games_per_year — replaces GROUP BY query on games table
        $gamesPerYear = DB::table('games')
            ->selectRaw('YEAR(date_time) as year, COUNT(*) as total')
            ->groupByRaw('YEAR(date_time)')
            ->orderBy('year')
            ->get()
            ->toArray();
        SystemStat::set('games_per_year', $gamesPerYear);

        // active_players_per_year — replaces UNION + COUNT DISTINCT query
        $activePerYear = DB::query()
            ->fromSub(function ($query) {
                $query->selectRaw('YEAR(date_time) as year, winner_id as player_id')
                    ->from('games')
                    ->unionAll(
                        DB::table('games')->selectRaw('YEAR(date_time) as year, loser_id as player_id')
                    );
            }, 'all_players')
            ->selectRaw('year, COUNT(DISTINCT player_id) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->toArray();
        SystemStat::set('active_players_per_year', $activePerYear);

        echo "system_stats: 5 keys saved.\n";
    }

    /**
     * Build player_stats table.
     * Stores: peak_rating, best_rank, current_streak, longest_win_streak, last_played_at.
     * Uses in-memory $stats and $ratings passed from EloService to avoid re-querying everything.
     */
    private function buildPlayerStats(array $stats, array $ratings): void
    {
        DB::table('player_stats')->truncate();

        // Get best rank per player from snapshots (already built with correct 15-game + activity filter)
        $bestRanks = DB::table('rating_snapshots')
            ->selectRaw('player_id, MIN(`rank`) as best_rank')
            ->groupBy('player_id')
            ->pluck('best_rank', 'player_id');

        // Calculate current live rank for each player (position among all players by rating)
        // We only do this for players with 15+ games — same as Rankings/Index
        $qualifiedRatings = array_filter(
            $ratings,
            fn($rating, $playerId) => ($stats[$playerId]['games_played'] ?? 0) >= 15,
            ARRAY_FILTER_USE_BOTH
        );
        arsort($qualifiedRatings);
        $liveRanks = [];
        $rank = 1;
        foreach ($qualifiedRatings as $playerId => $_) {
            $liveRanks[$playerId] = $rank++;
        }

        // Get streaks from rating_histories — must query DB since EloService doesn't track them
        $streaks = $this->calculateStreaksFromHistory();

        // Get peak rating per player from rating_histories
        $peakRatings = DB::table('rating_histories')
            ->selectRaw('player_id, MAX(rating_after) as peak_rating')
            ->groupBy('player_id')
            ->pluck('peak_rating', 'player_id');

        $now   = now();
        $batch = [];

        foreach ($stats as $playerId => $playerStats) {
            $liveRank     = $liveRanks[$playerId] ?? null;
            $snapshotBest = $bestRanks[$playerId] ?? null;

            // best_rank = minimum (best position) between live rank and historical snapshots
            $bestRank = collect([$liveRank, $snapshotBest])->filter()->min();

            $batch[] = [
                'player_id'          => $playerId,
                'peak_rating'        => $peakRatings[$playerId] ?? ($ratings[$playerId] ?? 0),
                'best_rank'          => $bestRank,
                'current_streak'     => $streaks[$playerId]['current'] ?? 0,
                'longest_win_streak' => $streaks[$playerId]['longest'] ?? 0,
                'last_played_at'     => $playerStats['last_played_at'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('player_stats')->insert($chunk);
        }

        echo "player_stats: " . count($batch) . " rows inserted.\n";
    }

    /**
     * Calculate current_streak and longest_win_streak for all players.
     * Queries rating_histories once and processes in PHP.
     */
    private function calculateStreaksFromHistory(): array
    {
        // Fetch all results ordered chronologically — one query for all players
        $rows = DB::table('rating_histories')
            ->select('player_id', 'result', 'played_at')
            ->orderBy('played_at')
            ->orderBy('id')
            ->get();

        $streaks = [];

        foreach ($rows as $row) {
            $id = $row->player_id;

            if (!isset($streaks[$id])) {
                $streaks[$id] = [
                    'current'    => 0,
                    'longest'    => 0,
                    'last_result' => null,
                ];
            }

            $isWin = $row->result === 'win';

            if ($streaks[$id]['last_result'] === null) {
                $streaks[$id]['current'] = $isWin ? 1 : -1;
            } elseif ($isWin && $streaks[$id]['current'] > 0) {
                $streaks[$id]['current']++;
            } elseif (!$isWin && $streaks[$id]['current'] < 0) {
                $streaks[$id]['current']--;
            } else {
                $streaks[$id]['current'] = $isWin ? 1 : -1;
            }

            if ($streaks[$id]['current'] > $streaks[$id]['longest']) {
                $streaks[$id]['longest'] = $streaks[$id]['current'];
            }

            $streaks[$id]['last_result'] = $row->result;
        }

        return $streaks;
    }

    /**
     * Build country_stats table.
     * Qualifies countries with 5+ active players (15+ games, active in last 12 months).
     * avg_rating = average of top 5 players by rating.
     * total_wins/losses = from ALL country players in last 12 months.
     */
    private function buildCountryStats(): void
    {
        DB::table('country_stats')->truncate();

        $lastGame = RatingHistory::max('played_at');
        if (!$lastGame) {
            echo "No games found, skipping country_stats.\n";
            return;
        }

        $since = Carbon::parse($lastGame)->subMonths(config('rankgim.inactive_months'));

        // Get active qualified players with their country and current rating
        $players = DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->whereNull('players.player_id')
            ->where('player_ratings.games_played', '>=', 15)
            ->whereNotIn('players.country_code', ['XX'])
            ->whereExists(function ($q) use ($since) {
                $q->from('rating_histories')
                    ->whereColumn('rating_histories.player_id', 'players.id')
                    ->where('rating_histories.played_at', '>=', $since);
            })
            ->select('players.id', 'players.country', 'players.country_code', 'player_ratings.rating')
            ->get();

        // Get wins/losses per player in last 12 months
        $gameCounts = DB::table('rating_histories')
            ->where('played_at', '>=', $since)
            ->whereIn('player_id', $players->pluck('id'))
            ->selectRaw('player_id, SUM(result = "win") as wins, SUM(result = "loss") as losses')
            ->groupBy('player_id')
            ->get()
            ->keyBy('player_id');

        // Group players by country
        $byCountry = $players->groupBy('country_code');

        $now   = now();
        $batch = [];

        foreach ($byCountry as $code => $countryPlayers) {
            // Only countries with 5+ qualified players
            if ($countryPlayers->count() < 5) {
                continue;
            }

            $top5      = $countryPlayers->sortByDesc('rating')->take(5);
            $avgRating = (int) round($top5->avg('rating'));

            $totalWins   = 0;
            $totalLosses = 0;
            foreach ($countryPlayers as $p) {
                $totalWins   += $gameCounts[$p->id]->wins ?? 0;
                $totalLosses += $gameCounts[$p->id]->losses ?? 0;
            }

            $winRatio = ($totalWins + $totalLosses) > 0
                ? (int) round($totalWins / ($totalWins + $totalLosses) * 100)
                : 0;

            $batch[] = [
                'country_code'  => $code,
                'country'       => $countryPlayers->first()->country,
                'player_count'  => $countryPlayers->count(),
                'avg_rating'    => $avgRating,
                'total_wins'    => $totalWins,
                'total_losses'  => $totalLosses,
                'win_ratio'     => $winRatio,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        if (!empty($batch)) {
            DB::table('country_stats')->insert($batch);
        }

        echo "country_stats: " . count($batch) . " countries inserted.\n";
    }

    /**
     * Build head_to_head table.
     * player_a_id is always LEAST(id1, id2), player_b_id always GREATEST — ensures unique pairs.
     * Uses a single query to aggregate all matchups from rating_histories.
     */
    private function buildHeadToHead(): void
    {
        DB::table('head_to_head')->truncate();

        // One query: self-join rating_histories to get all matchups
        // rh1 = winner side, rh2 = loser side
        $rows = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->selectRaw('
                LEAST(rh1.player_id, rh2.player_id) as player_a_id,
                GREATEST(rh1.player_id, rh2.player_id) as player_b_id,
                COUNT(*) as games_count,
                SUM(CASE WHEN rh1.player_id = LEAST(rh1.player_id, rh2.player_id) THEN 1 ELSE 0 END) as player_a_wins
            ')
            ->groupByRaw('LEAST(rh1.player_id, rh2.player_id), GREATEST(rh1.player_id, rh2.player_id)')
            ->get();

        $now   = now();
        $batch = $rows->map(fn($row) => [
            'player_a_id'  => $row->player_a_id,
            'player_b_id'  => $row->player_b_id,
            'games_count'  => $row->games_count,
            'player_a_wins' => $row->player_a_wins,
            'created_at'   => $now,
            'updated_at'   => $now,
        ])->toArray();

        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('head_to_head')->insert($chunk);
        }

        echo "head_to_head: " . count($batch) . " pairs inserted.\n";
    }
}