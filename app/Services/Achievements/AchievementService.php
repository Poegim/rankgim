<?php

namespace App\Services\Achievements;

use App\Services\Achievements\Checkers\ActivityChecker;
use App\Services\Achievements\Checkers\CalendarChecker;
use App\Services\Achievements\Checkers\CommunityChecker;
use App\Services\Achievements\Checkers\DramaChecker;
use App\Services\Achievements\Checkers\GamesChecker;
use App\Services\Achievements\Checkers\HistoryChecker;
use App\Services\Achievements\Checkers\PrecisionChecker;
use App\Services\Achievements\Checkers\PrestigeChecker;
use App\Services\Achievements\Checkers\RankingChecker;
use App\Services\Achievements\Checkers\RatingChecker;
use App\Services\Achievements\Checkers\RivalryChecker;
use App\Services\Achievements\Checkers\SecretChecker;
use App\Services\Achievements\Checkers\StreakChecker;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(
        private GamesChecker     $games,
        private ActivityChecker  $activity,
        private RankingChecker   $ranking,
        private RatingChecker    $rating,
        private StreakChecker    $streak,
        private RivalryChecker   $rivalry,
        private CommunityChecker $community,
        private HistoryChecker   $history,
        private DramaChecker     $drama,
        private CalendarChecker  $calendar,
        private PrecisionChecker $precision,
        private PrestigeChecker  $prestige,
        private SecretChecker    $secret,
    ) {}

    /**
     * Rebuild all player achievements from scratch.
     * Called from StatsService::rebuild() after recalculateAll().
     *
     * @param array $stats   [player_id => [games_played, wins, losses, draws, last_played_at]]
     * @param array $ratings [player_id => rating]
     */
    public function rebuild(array $stats, array $ratings): void
    {
        echo "Building achievements...\n";

        DB::table('player_achievements')->truncate();

        // Pre-load shared data used by multiple checkers — single queries upfront
        $sharedData = $this->loadSharedData($stats, $ratings);

        $batch = [];

        $batch = array_merge($batch, $this->games->check($stats, $sharedData));
        $batch = array_merge($batch, $this->activity->check($stats, $sharedData));
        $batch = array_merge($batch, $this->ranking->check($stats, $sharedData));
        $batch = array_merge($batch, $this->rating->check($stats, $ratings, $sharedData));
        $batch = array_merge($batch, $this->streak->check($stats, $sharedData));
        $batch = array_merge($batch, $this->rivalry->check($stats, $sharedData));
        $batch = array_merge($batch, $this->community->check($stats, $sharedData));
        $batch = array_merge($batch, $this->history->check($stats, $sharedData));
        $batch = array_merge($batch, $this->drama->check($stats, $ratings, $sharedData));
        $batch = array_merge($batch, $this->calendar->check($stats, $sharedData));
        $batch = array_merge($batch, $this->precision->check($stats, $sharedData));
        $batch = array_merge($batch, $this->prestige->check($stats, $sharedData));
        // $batch = array_merge($batch, $this->secret->check($stats, $ratings, $sharedData));

        // Bulk insert in chunks
        foreach (array_chunk($batch, 500) as $chunk) {
            DB::table('player_achievements')->insertOrIgnore($chunk);
        }

        echo "Achievements: " . count($batch) . " unlocked across all players.\n";
    }

    /**
     * Load data shared across multiple checkers to avoid duplicate queries.
     */
    private function loadSharedData(array $stats, array $ratings): array
    {
        $now = now();

        // All rating histories ordered chronologically — used by many checkers
        $histories = DB::table('rating_histories')
            ->select('player_id', 'game_id', 'result', 'rating_before', 'rating_after', 'rating_change', 'played_at')
            ->orderBy('played_at')
            ->orderBy('id')
            ->get()
            ->groupBy('player_id');

        // All rating snapshots ordered chronologically — used by ranking checker
        $snapshots = DB::table('rating_snapshots')
            ->select('player_id', 'rank', 'snapshot_date')
            ->orderBy('snapshot_date')
            ->get()
            ->groupBy('player_id');

        // All head_to_head pairs — used by rivalry checker
        $headToHead = DB::table('head_to_head')
            ->select('player_a_id', 'player_b_id', 'games_count', 'player_a_wins')
            ->get();

        // Player registration order — used by history checker (og, founding_father)
        $playerOrder = DB::table('players')
            ->whereNull('player_id')
            ->orderBy('id')
            ->pluck('id');

        // Tournament participation — used by community checker
        $tournamentCounts = DB::table('games')
            ->selectRaw('winner_id as player_id, COUNT(DISTINCT tournament_id) as count')
            ->groupBy('winner_id')
            ->unionAll(
                DB::table('games')
                    ->selectRaw('loser_id as player_id, COUNT(DISTINCT tournament_id) as count')
                    ->groupBy('loser_id')
            )
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->sum('count'));

        // Country per player — used by community and history checkers
        $playerCountries = DB::table('players')
            ->whereNull('player_id')
            ->pluck('country_code', 'id');

        // Player names — used by secret checker (initials achievement)
        $playerNames = DB::table('players')
            ->whereNull('player_id')
            ->pluck('name', 'id');

        // Game number per player at the time of each game — used to filter unplaced players
        $gameNumbers = DB::table('rating_histories')
            ->select('player_id', 'game_id')
            ->orderBy('played_at')
            ->orderBy('id')
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->values()->map(fn($r, $i) => [
                'game_id'     => $r->game_id,
                'game_number' => $i + 1,
            ])->keyBy('game_id'));

        return [
            'now'               => $now,
            'histories'         => $histories,
            'snapshots'         => $snapshots,
            'head_to_head'      => $headToHead,
            'player_order'      => $playerOrder,
            'tournament_counts' => $tournamentCounts,
            'player_countries'  => $playerCountries,
            'player_names'      => $playerNames,
            'game_numbers'      => $gameNumbers,
        ];
    }
}