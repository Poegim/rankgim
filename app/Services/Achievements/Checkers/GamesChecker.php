<?php

namespace App\Services\Achievements\Checkers;

class GamesChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];
        $now   = $sharedData['now'];

        foreach ($stats as $playerId => $s) {
            $games   = $s['games_played'] ?? 0;
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            // Games played milestones — unlocked_at = date of the Nth game
            $milestones = [
                15  => ['first_blood', 'd'],
                50  => ['apprentice',  'd'],
                100 => ['veteran',     'c'],
                250 => ['gladiator',   'b'],
                500 => ['warlord',     'a'],
            ];

            foreach ($milestones as $threshold => [$key, $tier]) {
                if ($games >= $threshold) {
                    // Date of the exact Nth game
                    $date = $history->values()->get($threshold - 1)->played_at ?? $now->toDateString();
                    $batch[] = $this->row($playerId, $key, $tier, $threshold, $date);
                }
            }

            // rookie_mistake — date of the first game
            $firstGame = $history->first();
            if ($firstGame->result === 'loss') {
                $batch[] = $this->row($playerId, 'rookie_mistake', 'd', null, $firstGame->played_at);
            }

            // perfect_start — date of the 10th game
            if ($history->count() >= 10) {
                $firstTen = $history->take(10);
                $allWins  = $firstTen->every(fn($h) => $h->result === 'win');
                if ($allWins) {
                    $date = $history->values()->get(9)->played_at ?? $now->toDateString();
                    $batch[] = $this->row($playerId, 'perfect_start', 'b', null, $date);
                }
            }
        }

        echo "GamesChecker: " . count($batch) . " achievements.\n";

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