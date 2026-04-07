<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class SecretChecker
{
    public function check(array $stats, array $ratings, array $sharedData): array
    {
        $batch          = [];
        $countryRegions = collect(config('countries'))->pluck('region', 'code');

        // Pre-load opponent countries per game with dates — one query for all players
        $opponentCountriesByPlayer = \DB::table('rating_histories as rh1')
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

        // Pre-load mirror matches for all players — one query
        $mirrorMatches = \DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->whereColumn('rh1.player_id', '!=', 'rh2.player_id');
            })
            ->whereColumn('rh1.rating_before', 'rh2.rating_before')
            ->where('rh1.rating_before', '!=', 1000)
            ->where('rh2.rating_before', '!=', 1000)
            ->select('rh1.player_id', 'rh1.played_at')
            ->orderBy('rh1.played_at')
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->first());

        $gameNumbers = $sharedData['game_numbers'];

        // Pre-load "how" losses for all players — one query
        $howLosses = \DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'loss')
                     ->where('rh2.result', '=', 'win');
            })
            ->whereRaw('rh1.rating_before - rh2.rating_before >= 500')
            ->select('rh1.player_id', 'rh1.game_id', 'rh1.played_at')
            ->orderBy('rh1.played_at')
            ->get()
            ->filter(function ($row) use ($gameNumbers) {
                $loserGameNumber  = $gameNumbers->get($row->player_id)?->get($row->game_id)['game_number'] ?? 0;
                return $loserGameNumber >= 30;
            })
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->first());

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $games       = $s['games_played'] ?? 0;
            $wins        = $s['wins'] ?? 0;
            $playerName  = $sharedData['player_names'][$playerId] ?? '';
            $countryCode = $sharedData['player_countries'][$playerId] ?? '';

            // ------------------------------------------------------------------
            // Seasonal — played a game on a specific date
            // unlocked_at = that date
            // ------------------------------------------------------------------
            $addedSeasonals = [];

            foreach ($history as $h) {
                $md = Carbon::parse($h->played_at)->format('m-d');

                $seasonals = [
                    '01-01' => ['new_year',   'a'],
                    '02-14' => ['valentines', 'a'],
                    '12-25' => ['christmas',  'a'],
                    '10-31' => ['halloween',  'a'],
                    '03-31' => ['sc_birthday','a'],
                    '11-30' => ['bw_birthday', 'a'],
                    '08-14' => ['remastered_birthday','a'],
                ];

                foreach ($seasonals as $trigger => [$key, $tier]) {
                    if ($md === $trigger && !isset($addedSeasonals[$key])) {
                        $batch[]              = $this->row($playerId, $key, $tier, null, $h->played_at);
                        $addedSeasonals[$key] = true;
                    }
                }
            }

            // ------------------------------------------------------------------
            // Mirror Match — played opponent with exact same rating_before
            // unlocked_at = date of that game
            // ------------------------------------------------------------------
            $mirror = $mirrorMatches->get($playerId);
            if ($mirror) {
                $batch[] = $this->row($playerId, 'mirror_match', 'd', null, $mirror->played_at);
            }

            // ------------------------------------------------------------------
            // Beast Mode — 666th game
            // unlocked_at = date of the 666th game
            // ------------------------------------------------------------------
            if ($games >= 666) {
                $date = $history->values()->get(665)->played_at ?? now()->toDateString();
                $batch[] = $this->row($playerId, 'beast_mode', 'b', 666, $date);
            }

            // ------------------------------------------------------------------
            // Millennium — 1000th game
            // unlocked_at = date of the 1000th game
            // ------------------------------------------------------------------
            if ($games >= 1000) {
                $date = $history->values()->get(999)->played_at ?? now()->toDateString();
                $batch[] = $this->row($playerId, 'millennium', 'a', 1000, $date);
            }

            // ------------------------------------------------------------------
            // Back to Square One — ended a game with exactly 1000 rating (not first game)
            // unlocked_at = date of that game
            // ------------------------------------------------------------------
            $backToSquareOne = $history->skip(1)->first(fn($h) => $h->rating_after === 1000);
            if ($backToSquareOne) {
                $batch[] = $this->row($playerId, 'back_to_square_one', 'c', null, $backToSquareOne->played_at);
            }

            // ------------------------------------------------------------------
            // Ironic — loss streaks
            // unlocked_at = date of the Nth consecutive loss
            // ------------------------------------------------------------------
            $currentStreak  = 0;
            $lossMilestones = [10 => null, 20 => null, 30 => null];

            foreach ($history as $h) {
                if ($h->result !== 'win') {
                    $currentStreak++;
                    foreach ($lossMilestones as $threshold => $date) {
                        if ($date === null && $currentStreak >= $threshold) {
                            $lossMilestones[$threshold] = $h->played_at;
                        }
                    }
                } else {
                    $currentStreak = 0;
                }
            }

            if ($lossMilestones[10]) $batch[] = $this->row($playerId, 'bad_day',               'd', null, $lossMilestones[10]);
            if ($lossMilestones[20]) $batch[] = $this->row($playerId, 'different_game',         'c', null, $lossMilestones[20]);
            if ($lossMilestones[30]) $batch[] = $this->row($playerId, 'dedicated_to_the_cause', 's', null, $lossMilestones[30]);

            // ------------------------------------------------------------------
            // How?! — lost to a player rated 500+ lower
            // unlocked_at = date of that game
            // ------------------------------------------------------------------
            $how = $howLosses->get($playerId);
            if ($how) {
                $batch[] = $this->row($playerId, 'how', 'd', null, $how->played_at);
            }
        }

        echo "SecretChecker: " . count($batch) . " achievements.\n";

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