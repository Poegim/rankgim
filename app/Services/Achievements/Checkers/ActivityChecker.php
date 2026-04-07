<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class ActivityChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];
        $now   = now();

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            // Build sorted unique active months (Y-m) with their last game date
            $monthsWithDates = $history
                ->groupBy(fn($h) => Carbon::parse($h->played_at)->format('Y-m'))
                ->sortKeys()
                ->map(fn($games) => $games->max('played_at'));

            $activeMonths = $monthsWithDates->keys();
            $totalMonths  = $activeMonths->count();

            // Total active months — unlocked_at = last game of the Nth active month
            $totalMilestones = [
                12 => ['regular',   'd'],
                24 => ['dedicated', 'c'],
                36 => ['committed', 'b'],
                48 => ['obsessed',  'a'],
                60 => ['immortal',  's'],
            ];

            foreach ($totalMilestones as $threshold => [$key, $tier]) {
                if ($totalMonths >= $threshold) {
                    $date = $monthsWithDates->values()->get($threshold - 1) ?? $now->toDateString();
                    $batch[] = $this->row($playerId, $key, $tier, $totalMonths, $date);
                }
            }

            // Consecutive months — find longest streak and when each milestone was hit
            $maxStreak     = 1;
            $currentStreak = 1;

            // Track when each consecutive milestone was first reached
            $streakMilestones = [3 => null, 6 => null, 12 => null, 18 => null, 24 => null];

            for ($i = 1; $i < $activeMonths->count(); $i++) {
                $prev = Carbon::parse($activeMonths[$i - 1] . '-01');
                $curr = Carbon::parse($activeMonths[$i] . '-01');

                if ($prev->addMonth()->format('Y-m') === $curr->format('Y-m')) {
                    $currentStreak++;
                    $maxStreak = max($maxStreak, $currentStreak);

                    // Record date when streak milestone was first hit
                    foreach ($streakMilestones as $threshold => $date) {
                        if ($date === null && $currentStreak >= $threshold) {
                            $streakMilestones[$threshold] = $monthsWithDates->values()->get($i);
                        }
                    }
                } else {
                    $currentStreak = 1;
                }
            }

            $consecutiveMilestones = [
                3  => ['on_a_roll',           'd'],
                6  => ['consistent',          'c'],
                12 => ['unstoppable_activity', 'b'],
                18 => ['iron_will', 'a'],
                24 => ['machine',             's'],
            ];

            foreach ($consecutiveMilestones as $threshold => [$key, $tier]) {
                if ($maxStreak >= $threshold && isset($streakMilestones[$threshold]) && $streakMilestones[$threshold]) {
                    $batch[] = $this->row($playerId, $key, $tier, $maxStreak, $streakMilestones[$threshold]);
                }
            }
        }

        echo "ActivityChecker: " . count($batch) . " achievements.\n";

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