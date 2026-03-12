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
    private function calculate(float $r1, float $r2, int $result): array
    {
        $we1 = $this->winningExpectancy($r1, $r2);

        if ($result === 1) {
            $r1_new = round($r1 + self::K_FACTOR * (1 - $we1));
            $r2_new = round($r2 - ($r1_new - $r1));
        } elseif ($result === 3) {
            $we2 = $this->winningExpectancy($r2, $r1);
            $r1_new = round($r1 + self::K_FACTOR * (0.5 - $we1));
            $r2_new = round($r2 + self::K_FACTOR * (0.5 - $we2));
        }

        return [
            'r1_new'    => $r1_new,
            'r2_new'    => $r2_new,
            'r1_change' => $r1_new - $r1,
            'r2_change' => $r2_new - $r2,
        ];
    }

    /**
     * Process a single game and update ratings and history.
     */
    public function processGame(Game $game): void
    {
        $winnerRating = PlayerRating::firstOrCreate(
            ['player_id' => $game->winner_id],
            ['rating' => self::DEFAULT_RATING]
        );

        $loserRating = PlayerRating::firstOrCreate(
            ['player_id' => $game->loser_id],
            ['rating' => self::DEFAULT_RATING]
        );

        $result = $this->calculate($winnerRating->rating, $loserRating->rating, $game->result);

        RatingHistory::create([
            'player_id'     => $game->winner_id,
            'game_id'       => $game->id,
            'rating_before' => $winnerRating->rating,
            'rating_after'  => $result['r1_new'],
            'rating_change' => $result['r1_change'],
            'result'        => 'win',
            'played_at'     => $game->date_time,
        ]);

        RatingHistory::create([
            'player_id'     => $game->loser_id,
            'game_id'       => $game->id,
            'rating_before' => $loserRating->rating,
            'rating_after'  => $result['r2_new'],
            'rating_change' => $result['r2_change'],
            'result'        => 'loss',
            'played_at'     => $game->date_time,
        ]);

        $winnerRating->update([
            'rating'       => $result['r1_new'],
            'games_played' => $winnerRating->games_played + 1,
            'wins'         => $winnerRating->wins + 1,
        ]);

        $loserRating->update([
            'rating'       => $result['r2_new'],
            'games_played' => $loserRating->games_played + 1,
            'losses'       => $loserRating->losses + 1,
        ]);
    }

    /**
     * Recalculate all ratings from scratch, ordered by date.
     * Use this when importing historical data or adding games with older dates.
     */
    public function recalculateAll(): void
    {
        DB::transaction(function () {
            echo "Truncating tables...\n";
            RatingHistory::query()->delete();
            PlayerRating::query()->delete();
            RatingSnapshot::query()->delete();

            // Remap alias IDs to main player IDs in games
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

            $total = Game::count();
            echo "Processing {$total} games...\n";

            // In-memory state
            $ratings = [];
            $stats = [];
            $historyBatch = [];
            $lastProcessedMonth = null;
            $snapshots = [];
            $processed = 0;

            Game::orderBy('date_time')->orderBy('id')->cursor()->each(
                function (Game $game) use (
                    &$ratings, &$stats, &$historyBatch,
                    &$lastProcessedMonth, &$snapshots, &$processed, $total
                ) {
                    $winnerId = $game->winner_id;
                    $loserId  = $game->loser_id;

                    // Initialize ratings if first time seeing player
                    if (!isset($ratings[$winnerId])) {
                        $ratings[$winnerId] = self::DEFAULT_RATING;
                        $stats[$winnerId]   = ['games_played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0];
                    }
                    if (!isset($ratings[$loserId])) {
                        $ratings[$loserId] = self::DEFAULT_RATING;
                        $stats[$loserId]   = ['games_played' => 0, 'wins' => 0, 'losses' => 0, 'draws' => 0];
                    }

                    $r1 = $ratings[$winnerId];
                    $r2 = $ratings[$loserId];
                    $result = $this->calculate($r1, $r2, $game->result);

                    // Save history to batch
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

                    // Update in-memory state
                    $ratings[$winnerId] = $result['r1_new'];
                    $ratings[$loserId]  = $result['r2_new'];
                    $stats[$winnerId]['games_played']++;
                    $stats[$winnerId]['wins']++;
                    $stats[$loserId]['games_played']++;
                    $stats[$loserId]['losses']++;

                    // Handle snapshots
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
                }
            );

            // Last month snapshot
            if ($lastProcessedMonth) {
                $snapshots = array_merge($snapshots, $this->buildSnapshot($ratings, $stats, $lastProcessedMonth));
                echo "Snapshot queued for {$lastProcessedMonth}\n";
            }

            // Build ratings batch
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

            echo "Saving " . count($historyBatch) . " history records...\n";
            foreach (array_chunk($historyBatch, 500) as $i => $chunk) {
                DB::table('rating_histories')->insert($chunk);
                echo "History chunk " . ($i + 1) . " saved...\n";
            }

            echo "Saving " . count($ratingsBatch) . " player ratings...\n";
            DB::table('player_ratings')->insert($ratingsBatch);
            echo "Player ratings saved!\n";

            echo "Saving " . count($snapshots) . " snapshots...\n";
            foreach (array_chunk($snapshots, 500) as $i => $chunk) {
                DB::table('rating_snapshots')->insert($chunk);
                echo "Snapshot chunk " . ($i + 1) . " saved...\n";
            }

            echo "Done!\n";
        });
    }

    private function buildSnapshot(array $ratings, array $stats, string $yearMonth): array
    {
        $snapshotDate = \Carbon\Carbon::parse($yearMonth)->endOfMonth()->toDateString();
        $now = now();

        arsort($ratings);
        $rank = 1;
        $batch = [];

        foreach ($ratings as $playerId => $rating) {
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