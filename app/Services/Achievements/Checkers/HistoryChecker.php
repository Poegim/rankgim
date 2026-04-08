<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistoryChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];
        $now   = now();

        // First 500 and first 50 players who actually have games
        $first500 = DB::table('players')
            ->whereNull('player_id')
            ->whereIn('id', DB::table('rating_histories')->distinct()->pluck('player_id'))
            ->orderBy('id')
            ->limit(500)
            ->pluck('id')
            ->flip();

        $first50 = $first500->take(50);

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $firstGameDate = $history->first()->played_at;
            $lastGameDate  = $history->last()->played_at;

            // OG — one of the first 500 players
            // unlocked_at = date of their first game
            if (isset($first500[$playerId])) {
                $batch[] = $this->row($playerId, 'og', 'c', $first500[$playerId] + 1, $firstGameDate);
            }

            // Founding Father — one of the first 50 players
            // unlocked_at = date of their first game
            if (isset($first50[$playerId])) {
                $batch[] = $this->row($playerId, 'founding_father', 'a', $first50[$playerId] + 1, $firstGameDate);
            }

            // Time Traveler — games from 3+ different years
            // unlocked_at = date of first game in the 3rd distinct year
            $yearDates = $history
                ->groupBy(fn($h) => Carbon::parse($h->played_at)->year)
                ->sortKeys()
                ->map(fn($games) => $games->min('played_at'));

            $years = $yearDates->count();

            if ($years >= 3) {
                $date = $yearDates->values()->get(2);
                $batch[] = $this->row($playerId, 'time_traveler', 'd', $years, $date);
            }

            // Old Breed — played a game in 2017 or earlier
            $firstYear = Carbon::parse($history->first()->played_at)->year;
            if ($firstYear <= 2017) {
                $batch[] = $this->row($playerId, 'old_breed', 'c', $firstYear, $history->first()->played_at);
            }

            // Before the Plague — played a game before 2020
            $beforePlague = $history->first(fn($h) => Carbon::parse($h->played_at)->year < 2020);
            if ($beforePlague) {
                $batch[] = $this->row($playerId, 'before_the_plague', 'c', null, $beforePlague->played_at);

                // Veteran of the Old World — reached 15 games before 2020
                $gamesBeforePlague = $history->filter(
                    fn($h) => Carbon::parse($h->played_at)->year < 2020
                )->values();

                if ($gamesBeforePlague->count() >= 15) {
                    $date    = $gamesBeforePlague->get(14)->played_at;
                    $batch[] = $this->row($playerId, 'before_the_plague_ranked', 'b', null, $date);
                }
            }

            // Patient Zero — played a game in 2020
            $in2020 = $history->filter(fn($h) => Carbon::parse($h->played_at)->year === 2020)->values();
            if ($in2020->isNotEmpty()) {
                $batch[] = $this->row($playerId, 'patient_zero', 'c', null, $in2020->first()->played_at);

                // Plague Rat — reached 15 games in 2020
                if ($in2020->count() >= 15) {
                    $date    = $in2020->get(14)->played_at;
                    $batch[] = $this->row($playerId, 'patient_zero_ranked', 'b', null, $date);
                }
            }

            // Plague Survivor — played a game before and after 2020
            if ($beforePlague) {
                $afterPlague = $history->first(fn($h) => Carbon::parse($h->played_at)->year > 2020);
                if ($afterPlague) {
                    $batch[] = $this->row($playerId, 'plague_survivor', 'a', null, $afterPlague->played_at);
                }
            }
        }

        echo "HistoryChecker: " . count($batch) . " achievements.\n";

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