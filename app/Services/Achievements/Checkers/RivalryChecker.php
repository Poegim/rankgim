<?php

namespace App\Services\Achievements\Checkers;

use Illuminate\Support\Facades\DB;

class RivalryChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        $playerRaces = $sharedData['player_races'];
        $validRaces  = ['Terran', 'Zerg', 'Protoss'];

        // Race slayer matchup definitions — [winner_race, loser_race] => key_prefix
        // Race slayer matchup definitions — winner_race, loser_race, key_prefix
        $raceSlayerMatchups = [
            ['winner' => 'Protoss', 'loser' => 'Zerg',    'prefix' => 'protoss_slayer_zerg'],
            ['winner' => 'Protoss', 'loser' => 'Terran',  'prefix' => 'protoss_slayer_terran'],
            ['winner' => 'Zerg',    'loser' => 'Protoss', 'prefix' => 'zerg_slayer_protoss'],
            ['winner' => 'Zerg',    'loser' => 'Terran',  'prefix' => 'zerg_slayer_terran'],
            ['winner' => 'Terran',  'loser' => 'Zerg',    'prefix' => 'terran_slayer_zerg'],
            ['winner' => 'Terran',  'loser' => 'Protoss', 'prefix' => 'terran_slayer_protoss'],
        ];

        // Build per-player rivalry stats with dates from rating_histories
        // We need dates so we load head_to_head per-game from histories
        $rivalryDates = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->whereColumn('rh1.player_id', '!=', 'rh2.player_id');
            })
            ->selectRaw('
                rh1.player_id as player_id,
                rh2.player_id as opponent_id,
                rh1.result as result,
                rh1.played_at as played_at
            ')
            ->orderBy('rh1.played_at')
            ->get()
            ->groupBy('player_id');

        foreach ($stats as $playerId => $s) {

            $playerRace  = $playerRaces->get($playerId);
            $playerGames = $rivalryDates->get($playerId);

            // Mirror Master — wins in mirror matchups (same race, no Unknown/Random)
            if (in_array($playerRace, $validRaces)) {
                $mirrorWins  = 0;
                $mirrorDates = [5 => null, 10 => null, 25 => null, 50 => null, 100 => null];

                foreach ($playerGames ?? [] as $game) {
                    $opponentRace = $playerRaces->get($game->opponent_id);
                    if ($game->result === 'win' && $opponentRace === $playerRace) {
                        $mirrorWins++;
                        foreach ($mirrorDates as $threshold => $date) {
                            if ($date === null && $mirrorWins >= $threshold) {
                                $mirrorDates[$threshold] = $game->played_at;
                            }
                        }
                    }
                }

                if ($mirrorDates[5])   $batch[] = $this->row($playerId, 'mirror_master_d', 'd', $mirrorWins, $mirrorDates[5]);
                if ($mirrorDates[10])  $batch[] = $this->row($playerId, 'mirror_master_c', 'c', $mirrorWins, $mirrorDates[10]);
                if ($mirrorDates[25])  $batch[] = $this->row($playerId, 'mirror_master_b', 'b', $mirrorWins, $mirrorDates[25]);
                if ($mirrorDates[50])  $batch[] = $this->row($playerId, 'mirror_master_a', 'a', $mirrorWins, $mirrorDates[50]);
                if ($mirrorDates[100]) $batch[] = $this->row($playerId, 'mirror_master_s', 's', $mirrorWins, $mirrorDates[100]);
            }

            // Race Slayer — wins against a specific race
            if (in_array($playerRace, $validRaces)) {
                foreach ($raceSlayerMatchups as $matchup) {
                    if ($playerRace !== $matchup['winner']) continue;
                    $loserRace = $matchup['loser'];
                    $keyPrefix = $matchup['prefix'];

                    $wins  = 0;
                    $dates = [5 => null, 10 => null, 25 => null, 50 => null, 100 => null];

                    foreach ($playerGames ?? [] as $game) {
                        $opponentRace = $playerRaces->get($game->opponent_id);
                        if ($game->result === 'win' && $opponentRace === $loserRace) {
                            $wins++;
                            foreach ($dates as $threshold => $date) {
                                if ($date === null && $wins >= $threshold) {
                                    $dates[$threshold] = $game->played_at;
                                }
                            }
                        }
                    }

                    if ($dates[5])   $batch[] = $this->row($playerId, $keyPrefix . '_d', 'd', $wins, $dates[5]);
                    if ($dates[10])  $batch[] = $this->row($playerId, $keyPrefix . '_c', 'c', $wins, $dates[10]);
                    if ($dates[25])  $batch[] = $this->row($playerId, $keyPrefix . '_b', 'b', $wins, $dates[25]);
                    if ($dates[50])  $batch[] = $this->row($playerId, $keyPrefix . '_a', 'a', $wins, $dates[50]);
                    if ($dates[100]) $batch[] = $this->row($playerId, $keyPrefix . '_s', 's', $wins, $dates[100]);
                }
            }

            if (!$playerGames) continue;

            // Group by opponent and track running counts with dates
            $opponents = $playerGames->groupBy('opponent_id');

            $maxWins   = 0;
            $maxLosses = 0;
            $maxTotal  = 0;

            // Dates for when each milestone was first hit per opponent
            $winDates   = [];
            $lossDates  = [];
            $totalDates = [];

            foreach ($opponents as $opponentId => $games) {
                $wins   = 0;
                $losses = 0;
                $total  = 0;

                foreach ($games as $game) {
                    $total++;
                    if ($game->result === 'win') {
                        $wins++;
                    } else {
                        $losses++;
                    }

                    // Record first time each milestone was hit against this opponent
                    foreach ([10, 20] as $threshold) {
                        if ($wins === $threshold && !isset($winDates[$threshold])) {
                            $winDates[$threshold] = $game->played_at;
                        }
                    }
                    foreach ([5, 10] as $threshold) {
                        if ($losses === $threshold && !isset($lossDates[$threshold])) {
                            $lossDates[$threshold] = $game->played_at;
                        }
                    }
                    foreach ([30, 50, 100] as $threshold) {
                        if ($total === $threshold && !isset($totalDates[$threshold])) {
                            $totalDates[$threshold] = $game->played_at;
                        }
                    }
                }

                $maxWins   = max($maxWins, $wins);
                $maxLosses = max($maxLosses, $losses);
                $maxTotal  = max($maxTotal, $total);
            }

            // Losses to same player
            if ($maxLosses >= 5  && isset($lossDates[5]))  $batch[] = $this->row($playerId, 'rival',        'd', $maxLosses, $lossDates[5]);
            if ($maxLosses >= 10 && isset($lossDates[10])) $batch[] = $this->row($playerId, 'cursed',       'c', $maxLosses, $lossDates[10]);

            // Wins against same player
            if ($maxWins >= 10 && isset($winDates[10])) $batch[] = $this->row($playerId, 'bully',       'c', $maxWins, $winDates[10]);
            if ($maxWins >= 20 && isset($winDates[20])) $batch[] = $this->row($playerId, 'executioner', 'b', $maxWins, $winDates[20]);

            // Total games against same player
            if ($maxTotal >= 30  && isset($totalDates[30]))  $batch[] = $this->row($playerId, 'the_rematch', 'b', $maxTotal, $totalDates[30]);
            if ($maxTotal >= 50  && isset($totalDates[50]))  $batch[] = $this->row($playerId, 'the_rivalry', 'a', $maxTotal, $totalDates[50]);
            if ($maxTotal >= 100 && isset($totalDates[100])) $batch[] = $this->row($playerId, 'the_feud',    's', $maxTotal, $totalDates[100]);
        }

        echo "RivalryChecker: " . count($batch) . " achievements.\n";

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