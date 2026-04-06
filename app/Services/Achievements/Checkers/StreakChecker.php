<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class StreakChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            // Win streaks — unlocked_at = date of the last win in the streak
            $maxStreak     = 0;
            $currentStreak = 0;

            // Track when each streak milestone was first reached
            $streakMilestones = [3 => null, 5 => null, 10 => null, 15 => null, 25 => null, 50 => null];

            foreach ($history as $h) {
                if ($h->result === 'win') {
                    $currentStreak++;

                    if ($currentStreak > $maxStreak) {
                        $maxStreak = $currentStreak;
                    }

                    // Record date when streak milestone was first hit
                    foreach ($streakMilestones as $threshold => $date) {
                        if ($date === null && $currentStreak >= $threshold) {
                            $streakMilestones[$threshold] = $h->played_at;
                        }
                    }
                } else {
                    $currentStreak = 0;
                }
            }

            $milestones = [
                3  => ['hat_trick',          'd'],
                5  => ['hot_streak',         'd'],
                10 => ['rampage',            'c'],
                15 => ['unstoppable_streak', 'b'],
                25 => ['juggernaut',         'a'],
                50 => ['terminator',         's'],
            ];

            foreach ($milestones as $threshold => [$key, $tier]) {
                if ($maxStreak >= $threshold && $streakMilestones[$threshold]) {
                    $batch[] = $this->row($playerId, $key, $tier, $maxStreak, $streakMilestones[$threshold]);
                }
            }

            // Phoenix — return after 12+ months away
            // unlocked_at = date of the first game after the gap
            $dates = $history
                ->pluck('played_at')
                ->map(fn($d) => Carbon::parse($d))
                ->sort()
                ->values();

            if ($dates->count() < 2) continue;

            for ($i = 1; $i < $dates->count(); $i++) {
                $gap = $dates[$i - 1]->diffInMonths($dates[$i]);

                if ($gap >= 12) {
                    $batch[] = $this->row($playerId, 'phoenix', 'c', $gap, $dates[$i]->toDateString());
                    break;
                }
            }
        }

        echo "StreakChecker: " . count($batch) . " achievements.\n";

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