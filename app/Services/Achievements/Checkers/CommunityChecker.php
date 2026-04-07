<?php

namespace App\Services\Achievements\Checkers;

use Illuminate\Support\Facades\DB;

class CommunityChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];

        // Load opponent countries per game with dates for traveler/explorer/globetrotter
        $opponentGamesByPlayer = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->whereColumn('rh1.player_id', '!=', 'rh2.player_id');
            })
            ->join('players', 'players.id', '=', 'rh2.player_id')
            ->whereNull('players.player_id')
            ->select('rh1.player_id', 'players.country_code', 'rh1.played_at')
            ->orderBy('rh1.played_at')
            ->get()
            ->groupBy('player_id');

        // Load tournament participation with dates
        $tournamentGamesByPlayer = DB::table('games')
            ->select('winner_id as player_id', 'tournament_id', 'date_time')
            ->unionAll(
                DB::table('games')->select('loser_id as player_id', 'tournament_id', 'date_time')
            )
            ->orderBy('date_time')
            ->get()
            ->groupBy('player_id');

        foreach ($stats as $playerId => $s) {
            // ---------------------------------------------------------------
            // Traveler / Explorer / Globetrotter
            // unlocked_at = date of game when Nth unique country was reached
            // ---------------------------------------------------------------
            $playerGames = $opponentGamesByPlayer->get($playerId);

            if ($playerGames) {
                $seenCountries   = [];
                $countryDates    = [];
                $countryMilestones = [5 => null, 10 => null, 20 => null, 30 => null, 40 => null];

                foreach ($playerGames as $game) {
                    $code = $game->country_code;
                    if (!isset($seenCountries[$code])) {
                        $seenCountries[$code] = true;
                        $count = count($seenCountries);

                        foreach ($countryMilestones as $threshold => $date) {
                            if ($date === null && $count >= $threshold) {
                                $countryMilestones[$threshold] = $game->played_at;
                            }
                        }
                    }
                }

                $countryCount = count($seenCountries);

                if ($countryCount >= 5  && $countryMilestones[5])  $batch[] = $this->row($playerId, 'traveler',                 'd', $countryCount, $countryMilestones[5]);
                if ($countryCount >= 10 && $countryMilestones[10]) $batch[] = $this->row($playerId, 'explorer',                 'c', $countryCount, $countryMilestones[10]);
                if ($countryCount >= 20 && $countryMilestones[20]) $batch[] = $this->row($playerId, 'globetrotter',             'b', $countryCount, $countryMilestones[20]);
                if ($countryCount >= 30 && $countryMilestones[30]) $batch[] = $this->row($playerId, 'citizen_of_the_universe',  'a', $countryCount, $countryMilestones[30]);
                if ($countryCount >= 40 && $countryMilestones[40]) $batch[] = $this->row($playerId, 'koprulu_cartographer',     's', $countryCount, $countryMilestones[40]);
            }

            // ---------------------------------------------------------------
            // Tournament achievements
            // unlocked_at = date of first game in the Nth tournament
            // ---------------------------------------------------------------
            $playerTournaments = $tournamentGamesByPlayer->get($playerId);

            if ($playerTournaments) {
                $seenTournaments    = [];
                $tournamentDates    = [];
                $tournamentMilestones = [5 => null, 25 => null, 100 => null, 250 => null, 500 => null];
                foreach ($playerTournaments as $game) {
                    $tid = $game->tournament_id;
                    if (!isset($seenTournaments[$tid])) {
                        $seenTournaments[$tid] = true;
                        $count = count($seenTournaments);

                        foreach ($tournamentMilestones as $threshold => $date) {
                            if ($date === null && $count >= $threshold) {
                                $tournamentMilestones[$threshold] = $game->date_time;
                            }
                        }
                    }
                }

                $tournamentCount = count($seenTournaments);

                if ($tournamentCount >= 5   && $tournamentMilestones[5])   $batch[] = $this->row($playerId, 'circuit_player',        'd', $tournamentCount, $tournamentMilestones[5]);
                if ($tournamentCount >= 25  && $tournamentMilestones[25])  $batch[] = $this->row($playerId, 'road_warrior',          'c', $tournamentCount, $tournamentMilestones[25]);
                if ($tournamentCount >= 100 && $tournamentMilestones[100]) $batch[] = $this->row($playerId, 'legend_of_the_circuit', 'b', $tournamentCount, $tournamentMilestones[100]);
                if ($tournamentCount >= 250 && $tournamentMilestones[250]) $batch[] = $this->row($playerId, 'conqueror',             'a', $tournamentCount, $tournamentMilestones[250]);
                if ($tournamentCount >= 500 && $tournamentMilestones[500]) $batch[] = $this->row($playerId, 'war_boy',               's', $tournamentCount, $tournamentMilestones[500]);

            }
        }

        echo "CommunityChecker: " . count($batch) . " achievements.\n";

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