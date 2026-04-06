<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;

class SecretChecker
{
    /**
     * Check secret and ironic achievements.
     * - Seasonal: new_year, valentines, christmas, halloween
     * - Mirror Match: played opponent with exact same rating
     * - Beast Mode: 666th game
     * - Millennium: 1000th game
     * - Initials: name starts with same letter as country
     * - World Tour: played opponents from EU, NA, SA and Asia
     * - Back to Square One: ended a game with exactly 1000 rating
     * - Fifty Fifty: exactly 50% win rate after exactly 100 games
     * - Ironic: bad_day, different_game, dedicated_to_the_cause, how, rookie_mistake
     *
     * @param array $stats      [player_id => [...]]
     * @param array $ratings    [player_id => current_rating]
     * @param array $sharedData Pre-loaded shared data from AchievementService
     * @return array            Rows ready for bulk insert into player_achievements
     */
    public function check(array $stats, array $ratings, array $sharedData): array
    {
        $batch = [];
        $now   = $sharedData['now'];

        // Pre-load regions per country for world_tour check
        $countryRegions = collect(config('countries'))->pluck('region', 'code');

        // Pre-load opponent countries per game for world_tour
        $opponentCountriesByPlayer = \DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->whereColumn('rh1.player_id', '!=', 'rh2.player_id');
            })
            ->join('players', 'players.id', '=', 'rh2.player_id')
            ->whereNull('players.player_id')
            ->selectRaw('rh1.player_id, players.country_code')
            ->distinct()
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->pluck('country_code')->unique());

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            $games      = $s['games_played'] ?? 0;
            $wins       = $s['wins'] ?? 0;
            $playerName = $sharedData['player_names'][$playerId] ?? '';
            $countryCode = $sharedData['player_countries'][$playerId] ?? '';

            // ------------------------------------------------------------------
            // Seasonal — played a game on a specific date
            // ------------------------------------------------------------------
            $dates = $history->map(fn($h) => Carbon::parse($h->played_at));

            foreach ($dates as $date) {
                $md = $date->format('m-d');

                if ($md === '01-01') $batch[] = $this->row($playerId, 'new_year',   'd', null, $now);
                if ($md === '02-14') $batch[] = $this->row($playerId, 'valentines', 'd', null, $now);
                if ($md === '12-25') $batch[] = $this->row($playerId, 'christmas',  'd', null, $now);
                if ($md === '10-31') $batch[] = $this->row($playerId, 'halloween',  'd', null, $now);
            }

            // Deduplicate seasonal — collect keys added so far and skip duplicates
            // (handled by unique constraint in DB, but avoid flooding batch)

            // ------------------------------------------------------------------
            // Mirror Match — played opponent with exact same rating_before
            // ------------------------------------------------------------------
            $hasMirror = \DB::table('rating_histories as rh1')
                ->join('rating_histories as rh2', function ($join) {
                    $join->on('rh1.game_id', '=', 'rh2.game_id')
                         ->whereColumn('rh1.player_id', '!=', 'rh2.player_id');
                })
                ->where('rh1.player_id', $playerId)
                ->whereColumn('rh1.rating_before', 'rh2.rating_before')
                ->exists();

            if ($hasMirror) {
                $batch[] = $this->row($playerId, 'mirror_match', 'd', null, $now);
            }

            // ------------------------------------------------------------------
            // Beast Mode — 666th game
            // ------------------------------------------------------------------
            if ($games >= 666) {
                $batch[] = $this->row($playerId, 'beast_mode', 'd', 666, $now);
            }

            // ------------------------------------------------------------------
            // Millennium — 1000th game
            // ------------------------------------------------------------------
            if ($games >= 1000) {
                $batch[] = $this->row($playerId, 'millennium', 'b', 1000, $now);
            }

            // ------------------------------------------------------------------
            // Initials — name starts with same letter as country code
            // ------------------------------------------------------------------
            if (
                $playerName &&
                $countryCode &&
                strtoupper(substr($playerName, 0, 1)) === strtoupper(substr($countryCode, 0, 1))
            ) {
                $batch[] = $this->row($playerId, 'initials', 'd', null, $now);
            }

            // ------------------------------------------------------------------
            // World Tour — played opponents from EU, NA, SA and Asia
            // ------------------------------------------------------------------
            $opponentCountries = $opponentCountriesByPlayer->get($playerId, collect());
            $regions = $opponentCountries
                ->map(fn($code) => $countryRegions[$code] ?? null)
                ->filter()
                ->unique();

            $requiredRegions = ['Europe', 'North America', 'South America', 'Asia'];
            $hasAllRegions   = collect($requiredRegions)->every(fn($r) => $regions->contains($r));

            if ($hasAllRegions) {
                $batch[] = $this->row($playerId, 'world_tour', 'c', null, $now);
            }

            // ------------------------------------------------------------------
            // Back to Square One — ended a game with exactly 1000 rating
            // ------------------------------------------------------------------
            $backToSquareOne = $history
                ->skip(1) // Skip first game — everyone starts at 1000
                ->contains(fn($h) => $h->rating_after === 1000);

            if ($backToSquareOne) {
                $batch[] = $this->row($playerId, 'back_to_square_one', 'c', null, $now);
            }

            // ------------------------------------------------------------------
            // Fifty Fifty — exactly 50% win rate after exactly 100 games
            // ------------------------------------------------------------------
            if ($games === 100 && $wins === 50) {
                $batch[] = $this->row($playerId, 'fifty_fifty', 'c', null, $now);
            }

            // ------------------------------------------------------------------
            // Ironic — loss streaks
            // ------------------------------------------------------------------
            $results        = $history->pluck('result')->toArray();
            $maxLossStreak  = 0;
            $currentStreak  = 0;

            foreach ($results as $result) {
                if ($result !== 'win') {
                    $currentStreak++;
                    $maxLossStreak = max($maxLossStreak, $currentStreak);
                } else {
                    $currentStreak = 0;
                }
            }

            if ($maxLossStreak >= 10) $batch[] = $this->row($playerId, 'bad_day',              'd', $maxLossStreak, $now);
            if ($maxLossStreak >= 20) $batch[] = $this->row($playerId, 'different_game',        'c', $maxLossStreak, $now);
            if ($maxLossStreak >= 30) $batch[] = $this->row($playerId, 'dedicated_to_the_cause','b', $maxLossStreak, $now);

            // How?! — lost to a player rated 500+ lower
            $howExists = \DB::table('rating_histories as rh1')
                ->join('rating_histories as rh2', function ($join) {
                    $join->on('rh1.game_id', '=', 'rh2.game_id')
                         ->where('rh1.result', '=', 'loss')
                         ->where('rh2.result', '=', 'win');
                })
                ->where('rh1.player_id', $playerId)
                ->whereRaw('rh1.rating_before - rh2.rating_before >= 500')
                ->exists();

            if ($howExists) {
                $batch[] = $this->row($playerId, 'how', 'd', null, $now);
            }

            // rookie_mistake is handled in GamesChecker — skip here
        }

        // Deduplicate batch — seasonal achievements can be added multiple times
        // if player played on the same date in multiple years
        $seen  = [];
        $batch = array_filter($batch, function ($row) use (&$seen) {
            $key = $row['player_id'] . '_' . $row['key'];
            if (isset($seen[$key])) return false;
            $seen[$key] = true;
            return true;
        });

        echo "SecretChecker: " . count($batch) . " achievements.\n";

        return array_values($batch);
    }

    private function row(int $playerId, string $key, string $tier, ?int $value, $now): array
    {
        return [
            'player_id'   => $playerId,
            'key'         => $key,
            'tier'        => $tier,
            'value'       => $value,
            'unlocked_at' => $now->toDateString(),
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }
}