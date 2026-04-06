<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class PrecisionChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        foreach ($stats as $playerId => $s) {
            $games = $s['games_played'] ?? 0;
            $wins  = $s['wins'] ?? 0;

            // Efficient — 70%+ win rate with at least 50 games
            // unlocked_at = date of the 50th game
            if ($games >= 50 && ($wins / $games) >= 0.70) {
                $history = $sharedData['histories']->get($playerId);
                if ($history) {
                    $date = $history->values()->get(49)->played_at ?? now()->toDateString();
                    $batch[] = $this->row($playerId, 'efficient', 'b', round(($wins / $games) * 100), $date);
                }
            }

            // Consistent Killer — 60%+ win rate for 3 consecutive months (min 5 games per month)
            // unlocked_at = last game of the 3rd qualifying month
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $byMonth = $history
                ->groupBy(fn($h) => Carbon::parse($h->played_at)->format('Y-m'))
                ->sortKeys();

            $consecutiveMonths = 0;

            foreach ($byMonth as $month => $games) {
                $monthGames = $games->count();
                $monthWins  = $games->where('result', 'win')->count();

                if ($monthGames >= 5 && ($monthWins / $monthGames) >= 0.60) {
                    $consecutiveMonths++;

                    if ($consecutiveMonths >= 3) {
                        $date = $games->max('played_at');
                        $batch[] = $this->row($playerId, 'consistent_killer', 'b', $consecutiveMonths, $date);
                        break;
                    }
                } else {
                    $consecutiveMonths = 0;
                }
            }
        }

        echo "PrecisionChecker: " . count($batch) . " achievements.\n";

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