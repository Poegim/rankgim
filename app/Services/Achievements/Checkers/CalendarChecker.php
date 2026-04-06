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

            // Marathon — 10+ games in a single day
            // unlocked_at = that day
            foreach ($byDay as $date => $games) {
                if ($games->count() >= 10) {
                    $batch[] = $this->row($playerId, 'marathon', 'c', $games->count(), $date);
                    break;
                }
            }

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

                if ($uniqueDays->count() >= 20) {
                    $date = $uniqueDays->get(19); // Date of the 20th unique day
                    $batch[] = $this->row($playerId, 'workaholic', 'b', $uniqueDays->count(), $date);
                    break;
                }
            }

            // Weekend Warrior — 5+ games on a single Saturday or Sunday
            // unlocked_at = that day
            foreach ($byDay as $date => $games) {
                $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                if (($dayOfWeek === 0 || $dayOfWeek === 6) && $games->count() >= 5) {
                    $batch[] = $this->row($playerId, 'weekend_warrior', 'd', $games->count(), $date);
                    break;
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