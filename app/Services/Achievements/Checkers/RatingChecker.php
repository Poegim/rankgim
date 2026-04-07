<?php

namespace App\Services\Achievements\Checkers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RatingChecker
{
    public function check(array $stats, array $ratings, array $sharedData): array
    {
        $batch       = [];
        $gameNumbers = $sharedData['game_numbers'];

        $milestones = [
            1100 => ['rising',       'd'],
            1200 => ['solid',        'd'],
            1300 => ['strong',       'c'],
            1400 => ['dangerous',    'c'],
            1500 => ['fearsome',     'b'],
            1600 => ['dominant',     'b'],
            1700 => ['elite_rating', 'a'],
            1800 => ['terrifying',   'a'],
            1900 => ['legendary',    's'],
            2000 => ['mythical',     's'],
            2100 => ['transcendent', 's'],
            2200 => ['otherworldly', 's'],
            2300 => ['god_tier',     's'],
        ];

        foreach ($stats as $playerId => $s) {
            $history = $sharedData['histories']->get($playerId);
            if (!$history || $history->isEmpty()) continue;

            // Rating milestones — unlocked_at = date of first game where rating_after >= threshold
            foreach ($milestones as $threshold => [$key, $tier]) {
                $first = $history->first(fn($h) => $h->rating_after >= $threshold);
                if ($first) {
                    $batch[] = $this->row($playerId, $key, $tier, $threshold, $first->played_at);
                }
            }

            // Rocket — gain 100+ rating points in a single calendar month
            // unlocked_at = last game of the month where the gain happened
            $byMonth = $history->groupBy(
                fn($h) => Carbon::parse($h->played_at)->format('Y-m')
            );

            foreach ($byMonth as $month => $games) {
                $gain = $games->max('rating_after') - $games->min('rating_before');
                if ($gain >= 100) {
                    $date = $games->max('played_at');
                    $batch[] = $this->row($playerId, 'rocket', 'b', $gain, $date);
                    break;
                }
            }
        }

        // Giant Slayer / David vs Goliath — unlocked_at = date of the upset game
        $upsets = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->selectRaw('
                rh1.player_id as winner_id,
                rh2.player_id as loser_id,
                rh1.rating_before as winner_rating,
                rh2.rating_before as loser_rating,
                (rh2.rating_before - rh1.rating_before) as diff,
                rh1.game_id as game_id,
                rh1.played_at
            ')
            ->having('diff', '>=', 200)
            ->get()
            ->filter(function ($row) use ($gameNumbers) {
                $winnerGameNumber = $gameNumbers->get($row->winner_id)?->get($row->game_id)['game_number'] ?? 0;
                $loserGameNumber  = $gameNumbers->get($row->loser_id)?->get($row->game_id)['game_number'] ?? 0;
                return $winnerGameNumber >= 30 && $loserGameNumber >= 30;
            })
            ->groupBy('winner_id');

        foreach ($upsets as $playerId => $playerUpsets) {
            // Giant Slayer — first game where diff >= 200
            $first200 = $playerUpsets->first(fn($u) => $u->diff >= 200);
            if ($first200) {
                $batch[] = $this->row($playerId, 'giant_slayer', 'c', $first200->diff, $first200->played_at);
            }

            // David vs Goliath — first game where diff >= 300
            $first300 = $playerUpsets->first(fn($u) => $u->diff >= 300);
            if ($first300) {
                $batch[] = $this->row($playerId, 'david_vs_goliath', 'b', $first300->diff, $first300->played_at);
            }
        }

        echo "RatingChecker: " . count($batch) . " achievements.\n";

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