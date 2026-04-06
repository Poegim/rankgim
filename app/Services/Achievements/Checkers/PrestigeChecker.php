<?php

namespace App\Services\Achievements\Checkers;

class PrestigeChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        foreach ($stats as $playerId => $s) {
            $snapshots = $sharedData['snapshots']->get($playerId);
            if (!$snapshots || $snapshots->isEmpty()) continue;

            // The GOAT — #1 for a total of 12 months
            // unlocked_at = date of the 12th month at #1
            $top1Snapshots = $snapshots
                ->where('rank', 1)
                ->sortBy('snapshot_date')
                ->values();

            if ($top1Snapshots->count() >= 12) {
                $date = $top1Snapshots->get(11)->snapshot_date;
                $batch[] = $this->row($playerId, 'the_goat', 's', $top1Snapshots->count(), $date);
            }
        }

        echo "PrestigeChecker: " . count($batch) . " achievements.\n";

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