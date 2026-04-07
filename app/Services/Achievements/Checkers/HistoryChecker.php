<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class HistoryChecker
{
    public function check(array $stats, array $sharedData): array
    {
        $batch = [];
        $now   = now();

        $playerOrder = $sharedData['player_order'];
        $first500    = $playerOrder->take(500)->flip();
        $first50     = $playerOrder->take(50)->flip();

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
                $batch[] = $this->row($playerId, 'founding_father', 'b', $first50[$playerId] + 1, $firstGameDate);
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
            }

            // Patient Zero — played a game in 2020
            $in2020 = $history->first(fn($h) => Carbon::parse($h->played_at)->year === 2020);
            if ($in2020) {
                $batch[] = $this->row($playerId, 'patient_zero', 'c', null, $in2020->played_at);
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