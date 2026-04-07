<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DramaChecker
{
    public function check(array $stats, array $ratings, array $sharedData): array
    {
        $batch       = [];
        $gameNumbers = $sharedData['game_numbers'];

        // Pre-compute top 3 player ids from snapshots
        $top3PlayerIds = $sharedData['snapshots']
            ->filter(fn($snaps) => $snaps->where('rank', '<=', 3)->isNotEmpty())
            ->keys()
            ->flip();

        // Pre-compute upset wins with game_numbers filter
        $upsetWins = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->selectRaw('
                rh1.player_id as winner_id,
                rh2.player_id as loser_id,
                rh1.rating_before as winner_rating,
                rh2.rating_before as loser_rating,
                (rh2.rating_before - rh1.rating_before) as diff,
                rh1.game_id as game_id,
                rh1.played_at
            ')
            ->having('diff', '>=', 200)
            ->orderBy('rh1.played_at')
            ->get()
            ->filter(function ($row) use ($gameNumbers) {
                $winnerGameNumber = $gameNumbers->get($row->winner_id)?->get($row->game_id)['game_number'] ?? 0;
                $loserGameNumber  = $gameNumbers->get($row->loser_id)?->get($row->game_id)['game_number'] ?? 0;
                return $winnerGameNumber >= 30 && $loserGameNumber >= 30;
            })
            ->groupBy('winner_id');

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $results = $history->values();

            // ---------------------------------------------------------------
            // Redemption — lose 5 in a row then win 5 in a row
            // unlocked_at = date of the 5th consecutive win after the losing streak
            // ---------------------------------------------------------------
            $loseStreak = 0;
            $redeemed   = false;

            for ($i = 0; $i < $results->count(); $i++) {
                if ($results[$i]->result !== 'win') {
                    $loseStreak++;
                    continue;
                }

                if ($loseStreak >= 5) {
                    $winStreak = 0;
                    for ($j = $i; $j < $results->count(); $j++) {
                        if ($results[$j]->result === 'win') {
                            $winStreak++;
                        } else {
                            break;
                        }
                    }

                    if ($winStreak >= 5) {
                        $date = $results[$i + 4]->played_at ?? $results[$i]->played_at;
                        $batch[] = $this->row($playerId, 'redemption', 'c', null, $date);
                        $redeemed = true;
                        break;
                    }
                }

                $loseStreak = 0;
            }

            // ---------------------------------------------------------------
            // Against All Odds — beat a top 3 player while outside top 50
            // unlocked_at = date of that game
            // ---------------------------------------------------------------
            $playerSnapshots = $sharedData['snapshots']->get($playerId);

            if ($playerSnapshots) {
                $outsideTop50Months = $playerSnapshots
                    ->where('rank', '>', 50)
                    ->pluck('snapshot_date')
                    ->map(fn($d) => Carbon::parse($d)->format('Y-m'))
                    ->flip();

                foreach ($history as $h) {
                    if ($h->result !== 'win') continue;

                    $month = Carbon::parse($h->played_at)->format('Y-m');
                    if (!isset($outsideTop50Months[$month])) continue;

                    $opponentId = \DB::table('rating_histories')
                        ->where('game_id', $h->game_id)
                        ->where('player_id', '!=', $playerId)
                        ->value('player_id');

                    if (!$opponentId) continue;

                    // Check top 10
                    $opponentSnaps = $sharedData['snapshots']->get($opponentId);
                    if (!$opponentSnaps) continue;

                    $opponentBestRank = $opponentSnaps->min('rank');

                    if ($opponentBestRank <= 3 && isset($top3PlayerIds[$opponentId])) {
                        $batch[] = $this->row($playerId, 'against_all_odds', 's', null, $h->played_at);
                        break;
                    } elseif ($opponentBestRank <= 10) {
                        $batch[] = $this->row($playerId, 'against_all_odds_10', 'a', null, $h->played_at);
                        break;
                    }
                }
            }

            // ---------------------------------------------------------------
            // Upset King — beat a player 200+ higher 5 times
            // unlocked_at = date of the 5th such upset
            // ---------------------------------------------------------------
            $playerUpsets = $upsetWins->get($playerId);
            if ($playerUpsets && $playerUpsets->count() >= 5) {
                $date = $playerUpsets->values()->get(4)->played_at;
                $batch[] = $this->row($playerId, 'upset_king', 'a', $playerUpsets->count(), $date);
            }
        }

        echo "DramaChecker: " . count($batch) . " achievements.\n";

        return $batch;
    }

    private function row(int $playerId, string $key, string $tier, ?int $value, string $unlockedAt): array
    {
        $now = now();
        return [
            'player_id'   => $playerId,
            'key'         => $key,
            'tier'        => $tier,
            'value'       => $value,
            'unlocked_at' => $unlockedAt,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }
}