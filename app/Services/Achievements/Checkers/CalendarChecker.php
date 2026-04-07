<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class CalendarChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $byDay = $history->groupBy(
                fn($h) => Carbon::parse($h->played_at)->toDateString()
            );

            // Workaholic — played on 20+ different days in a single month
            // unlocked_at = last day of that month when 20th day was reached
            $byMonth = $history->groupBy(
                fn($h) => Carbon::parse($h->played_at)->format('Y-m')
            );

            foreach ($byMonth as $month => $games) {
                $uniqueDays = $games
                    ->map(fn($h) => Carbon::parse($h->played_at)->toDateString())
                    ->unique()
                    ->sort()
                    ->values();

                $count = $uniqueDays->count();
                $milestones = [
                    20 => ['workaholic',  's'],
                    10 => ['no_days_off', 'b'],
                    5  => ['night_shift', 'c'],
                ];

                foreach ($milestones as $threshold => [$key, $tier]) {
                    if ($count >= $threshold) {
                        $date = $uniqueDays->get($threshold - 1);
                        $batch[] = $this->row($playerId, $key, $tier, $count, $date);
                        break 2;
                    }
                }
            }
        }

        echo "CalendarChecker: " . count($batch) . " achievements.\n";

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