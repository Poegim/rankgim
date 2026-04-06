<?php

namespace App\Services;

use App\Models\Game;
use App\Models\PlayerRating;
use App\Models\RatingHistory;
use App\Models\RatingSnapshot;
use Illuminate\Support\Facades\DB;

class EloService
{
    const K_FACTOR = 40;
    const SCALE = 400;
    const DEFAULT_RATING = 1000;

    /**
     * Calculate winning expectancy for player A against player B.
     */
    private function winningExpectancy(float $ratingA, float $ratingB): float
    {
        return 1 / (pow(10, -($ratingA - $ratingB) / self::SCALE) + 1);
    }

    /**
     * Calculate new ratings after a game.
     * Result: 1 = r1 wins, 3 = draw
     */
    private function calculate(float $r1, float $r2, int $result, int $k1 = self::K_FACTOR, int $k2 = self::K_FACTOR): array
    {
        $we1 = $this->winningExpectancy($r1, $r2);

        if ($result === 1) {
            $r1_new = round($r1 + $k1 * (1 - $we1));
            $r2_new = round($r2 + $k2 * (0 - (1 - $we1)));
        } elseif ($result === 3) {
            $we2 = $this->winningExpectancy($r2, $r1);
            $r1_new = round($r1 + $k1 * (0.5 - $we1));
            $r2_new = round($r2 + $k2 * (0.5 - $we2));
        }

        return [
            'r1_new'    => $r1_new,
            'r2_new'    => $r2_new,
            'r1_change' => $r1_new - $r1,
            'r2_change' => $r2_new - $r2,
        ];
    }

    /**
     * Recalculate all ratings from scratch, ordered by date.
     * Use this when importing historical data or adding games with older dates.
     * After this completes, StatsService::rebuild() is called automatically.
     */
    public function recalculateAll(): void
    {
        // Step 1: Clear tables and remap aliases in a transaction
        DB::transaction(function () {
            echo "Truncating tables...\n";
            RatingHistory::query()->delete();
            PlayerRating::query()->delete();
            RatingSnapshot::query()->delete();

            echo "Remapping alias players in games...\n";
            $aliasMap = DB::table('players')
                ->whereNotNull('player_id')
                ->pluck('player_id', 'id');

            if ($aliasMap->isNotEmpty()) {
                DB::table('games')
                    ->whereIn('winner_id', $aliasMap->keys())
                    ->update(['winner_id' => DB::raw('(SELECT player_id FROM players WHERE id = games.winner_id AND player_id IS NOT NULL)')]);
                DB::table('games')
                    ->whereIn('loser_id', $aliasMap->keys())
                    ->update(['loser_id' => DB::raw('(SELECT player_id FROM players WHERE id = games.loser_id AND player_id IS NOT NULL)')]);
                echo "Remapped " . $aliasMap->count() . " aliases.\n";
            }
        });

        // Step 2: Calculate ratings in-memory (no DB writes during iteration)
        $total = Game::count();
        echo "Processing {$total} games...\n";

        $ratings          = [];
        $stats            = [];
        $historyBatch     = [];
        $lastProcessedMonth = null;
        $snapshots        = [];
        $processed        = 0;

        Game::orderBy('date_time')->orderBy('id')->cursor()->each(
            function (Game $game) use (
                &$ratings, &$stats, &$historyBatch,
                &$lastProcessedMonth, &$snapshots, &$processed, $total
            ) {
                $winnerId = $game->winner_id;
                $loserId  = $game->loser_id;

                if (!isset($ratings[$winnerId])) {
                    $ratings[$winnerId] = self::DEFAULT_RATING;
                    $stats[$winnerId]   = [
                        'games_played'  => 0,
                        'wins'          => 0,
                        'losses'        => 0,
                        'draws'         => 0,
                        'last_played_at' => null,
                    ];
                }
                if (!isset($ratings[$loserId])) {
                    $ratings[$loserId] = self::DEFAULT_RATING;
                    $stats[$loserId]   = [
                        'games_played'  => 0,
                        'wins'          => 0,
                        'losses'        => 0,
                        'draws'         => 0,
                        'last_played_at' => null,
                    ];
                }

                $r1 = $ratings[$winnerId];
                $r2 = $ratings[$loserId];

                // Shield system: reduce K-factor impact when playing against unplaced players (<15 games)
                $kWinner = ($stats[$winnerId]['games_played'] < 15) ? 60 : self::K_FACTOR;
                $kLoser  = ($stats[$winnerId]['games_played'] < 15) ? 10 : self::K_FACTOR;

                $result = $this->calculate($r1, $r2, $game->result, $kWinner, $kLoser);

                $now = now();
                $historyBatch[] = [
                    'player_id'     => $winnerId,
                    'game_id'       => $game->id,
                    'rating_before' => $r1,
                    'rating_after'  => $result['r1_new'],
                    'rating_change' => $result['r1_change'],
                    'result'        => 'win',
                    'played_at'     => $game->date_time,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $historyBatch[] = [
                    'player_id'     => $loserId,
                    'game_id'       => $game->id,
                    'rating_before' => $r2,
                    'rating_after'  => $result['r2_new'],
                    'rating_change' => $result['r2_change'],
                    'result'        => 'loss',
                    'played_at'     => $game->date_time,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                $ratings[$winnerId] = $result['r1_new'];
                $ratings[$loserId]  = $result['r2_new'];

                $stats[$winnerId]['games_played']++;
                $stats[$winnerId]['wins']++;
                $stats[$winnerId]['last_played_at'] = $game->date_time;

                $stats[$loserId]['games_played']++;
                $stats[$loserId]['losses']++;
                $stats[$loserId]['last_played_at'] = $game->date_time;

                // Build monthly snapshot when month changes
                $gameMonth = \Carbon\Carbon::parse($game->date_time)->format('Y-m');
                if ($lastProcessedMonth !== null && $gameMonth !== $lastProcessedMonth) {
                    $snapshots = array_merge($snapshots, $this->buildSnapshot($ratings, $stats, $lastProcessedMonth));
                    echo "Snapshot queued for {$lastProcessedMonth}\n";
                }
                $lastProcessedMonth = $gameMonth;

                $processed++;
                if ($processed % 1000 === 0) {
                    echo "Processed {$processed}/{$total} games...\n";
                }

                // Flush history batch every 500 records to avoid memory issues
                if (count($historyBatch) >= 500) {
                    DB::table('rating_histories')->insert($historyBatch);
                    $historyBatch = [];
                }
            }
        );

        // Build final snapshot for the last month
        if ($lastProcessedMonth) {
            $snapshots = array_merge($snapshots, $this->buildSnapshot($ratings, $stats, $lastProcessedMonth));
            echo "Snapshot queued for {$lastProcessedMonth}\n";
        }

        $now = now();
        $ratingsBatch = [];
        foreach ($ratings as $playerId => $rating) {
            $ratingsBatch[] = [
                'player_id'    => $playerId,
                'rating'       => $rating,
                'games_played' => $stats[$playerId]['games_played'],
                'wins'         => $stats[$playerId]['wins'],
                'losses'       => $stats[$playerId]['losses'],
                'draws'        => $stats[$playerId]['draws'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // Step 3: Bulk inserts outside transaction with checks disabled for speed
        DB::statement('SET unique_checks=0');
        DB::statement('SET foreign_key_checks=0');

        // Flush any remaining history records
        if (!empty($historyBatch)) {
            echo "Saving remaining " . count($historyBatch) . " history records...\n";
            DB::table('rating_histories')->insert($historyBatch);
        }

        echo "Saving " . count($ratingsBatch) . " player ratings...\n";
        DB::table('player_ratings')->insert($ratingsBatch);

        echo "Saving " . count($snapshots) . " snapshots...\n";
        foreach (array_chunk($snapshots, 1000) as $i => $chunk) {
            DB::table('rating_snapshots')->insert($chunk);
            echo "Snapshot chunk " . ($i + 1) . " saved...\n";
        }

        DB::statement('SET unique_checks=1');
        DB::statement('SET foreign_key_checks=1');

        echo "Done! Running StatsService...\n";

        // Step 4: Rebuild all pre-computed stats tables
        app(\App\Services\StatsService::class)->rebuild($stats, $ratings);

        echo "All done!\n";
    }

    /**
     * Build a monthly ranking snapshot.
     * Only includes players who qualify at the time of the snapshot:
     * - 15+ games played
     * - active in the last 12 months relative to the snapshot date
     * This mirrors the exact same rules used in Rankings/Index.
     */
    private function buildSnapshot(array $ratings, array $stats, string $yearMonth): array
    {
        $snapshotDate = \Carbon\Carbon::parse($yearMonth)->endOfMonth()->toDateString();
        $cutoff       = \Carbon\Carbon::parse($snapshotDate)->subMonths(config('rankgim.inactive_months'))->toDateString();
        $now          = now();

        // Filter: 15+ games AND active in last 12 months from snapshot date
        $qualified = array_filter(
            $ratings,
            fn($rating, $playerId) =>
                ($stats[$playerId]['games_played'] ?? 0) >= 15 &&
                isset($stats[$playerId]['last_played_at']) &&
                $stats[$playerId]['last_played_at'] >= $cutoff,
            ARRAY_FILTER_USE_BOTH
        );

        arsort($qualified);
        $rank  = 1;
        $batch = [];

        foreach ($qualified as $playerId => $rating) {
            $batch[] = [
                'player_id'     => $playerId,
                'rating'        => $rating,
                'rank'          => $rank++,
                'games_played'  => $stats[$playerId]['games_played'],
                'wins'          => $stats[$playerId]['wins'],
                'losses'        => $stats[$playerId]['losses'],
                'draws'         => $stats[$playerId]['draws'],
                'snapshot_date' => $snapshotDate,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        return $batch;
    }
}