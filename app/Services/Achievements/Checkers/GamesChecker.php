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
                15   => ['showing_up',       'd'],
                75   => ['rocky',            'c'],
                250  => ['inception',        'b'],
                500  => ['there_is_no_spoon','a'],
                1000 => ['tears_in_rain',    's'],
            ];

            foreach ($milestones as $threshold => $milestone) {
                [$key, $tier] = $milestone;
                if ($games >= $threshold) {
                    $date    = $history->values()->get($threshold - 1)->played_at ?? $now->toDateString();
                    $batch[] = $this->row($playerId, $key, $tier, $threshold, $date);
                }
            }

            // rookie_mistake — date of the first game
            $firstGame = $history->first();
            if ($firstGame->result === 'loss') {
                $batch[] = $this->row($playerId, 'rookie_mistake', 'd', null, $firstGame->played_at);
            }

            // perfect_start series — unlocked_at = date of the Nth consecutive win from game 1
            $startMilestones = [3 => null, 5 => null, 10 => null];

            foreach ($history->take(10)->values() as $i => $h) {
                if ($h->result !== 'win') break;

                $gameNum = $i + 1;
                foreach ($startMilestones as $threshold => $date) {
                    if ($date === null && $gameNum >= $threshold) {
                        $startMilestones[$threshold] = $h->played_at;
                    }
                }
            }

            if ($startMilestones[3])  $batch[] = $this->row($playerId, 'early_pressure', 'b', null, $startMilestones[3]);
            if ($startMilestones[5])  $batch[] = $this->row($playerId, 'all_in',         'a', null, $startMilestones[5]);
            if ($startMilestones[10]) $batch[] = $this->row($playerId, 'perfect_start',  's', null, $startMilestones[10]);
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