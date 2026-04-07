<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class RankingChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];
        $now   = now();

        foreach ($stats as $playerId => $s) {
            $snapshots = $sharedData['snapshots']->get($playerId);

            if (!$snapshots || $snapshots->isEmpty()) continue;

            $sorted = $snapshots->sortBy('snapshot_date')->values();

            // ranked — date of first snapshot
            $batch[] = $this->row($playerId, 'ranked', 'd', null, $sorted->first()->snapshot_date);

            // Peak position milestones — unlocked_at = date of first snapshot at that rank or better
            $peakMilestones = [
                100 => ['top_100', 'd'],
                50  => ['top_50',  'c'],
                25  => ['top_25',  'b'],
                10  => ['elite',   'a'],
                3   => ['podium',  'a'],
                1   => ['the_best','s'],
            ];

            foreach ($peakMilestones as $threshold => [$key, $tier]) {
                $first = $sorted->first(fn($s) => $s->rank <= $threshold);
                if ($first) {
                    $batch[] = $this->row($playerId, $key, $tier, $first->rank, $first->snapshot_date);
                }
            }

            // Time in top 10 — unlocked_at = date of the Nth month in top 10
            $top10 = $sorted->where('rank', '<=', 10)->values();
            $monthsInTop10 = $top10->count();

            $top10Milestones = [
                3  => ['fixture',     'c'],
                6  => ['pillar',      'b'],
                12 => ['institution', 'a'],
                24 => ['monument',    's'],
            ];

            foreach ($top10Milestones as $threshold => [$key, $tier]) {
                if ($monthsInTop10 >= $threshold) {
                    $date = $top10->get($threshold - 1)->snapshot_date;
                    $batch[] = $this->row($playerId, $key, $tier, $monthsInTop10, $date);
                }
            }

            // Returns after inactivity — unlocked_at = date of first snapshot after the gap
            $top10Snapshots = $sorted->where('rank', '<=', 10)->values();

            if ($top10Snapshots->count() >= 2) {
                for ($i = 1; $i < $top10Snapshots->count(); $i++) {
                    $prev = Carbon::parse($top10Snapshots[$i - 1]->snapshot_date);
                    $curr = Carbon::parse($top10Snapshots[$i]->snapshot_date);
                    $gap  = $prev->diffInMonths($curr);

                    if ($gap >= 6) {
                        $date = $top10Snapshots[$i]->snapshot_date;
                        $batch[] = $this->row($playerId, 'kings_return',   'b', $gap, $date);
                    }
                    if ($gap >= 12) {
                        $batch[] = $this->row($playerId, 'legends_return', 'a', $gap, $date);
                    }
                    if ($gap >= 24) {
                        $batch[] = $this->row($playerId, 'ghosts_return',  's', $gap, $date);
                    }
                }
            }
        }

        echo "RankingChecker: " . count($batch) . " achievements.\n";

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