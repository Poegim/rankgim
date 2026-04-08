<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AchievementInsights extends Component
{
    public function render()
    {
        return view('livewire.admin.achievement-insights', [
            'data' => $this->gatherData(),
        ]);
    }

    private function gatherData(): array
    {
        // --- General counts ---
        $totalPlayers = DB::table('players')->whereNull('player_id')->count();
        $totalGames   = DB::table('games')->count();

        // --- Games played distribution ---
        $gamesDistribution = DB::table('player_ratings')
            ->selectRaw("
                CASE
                    WHEN games_played < 10   THEN '< 10'
                    WHEN games_played < 50   THEN '10–49'
                    WHEN games_played < 100  THEN '50–99'
                    WHEN games_played < 250  THEN '100–249'
                    WHEN games_played < 500  THEN '250–499'
                    WHEN games_played < 1000 THEN '500–999'
                    WHEN games_played < 2000 THEN '1000–1999'
                    ELSE '2000+'
                END as bucket,
                COUNT(*) as players,
                MIN(games_played) as sort_val
            ")
            ->groupBy('bucket')
            ->orderBy('sort_val')
            ->get();

        // --- Rating distribution ---
        $ratingDistribution = DB::table('player_ratings')
            ->selectRaw('FLOOR(rating / 100) * 100 as bucket, COUNT(*) as players')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        // --- Games per year ---
        $gamesPerYear = DB::table('games')
            ->selectRaw('YEAR(date_time) as year, COUNT(*) as games')
            ->groupByRaw('YEAR(date_time)')
            ->orderBy('year')
            ->get();

        // --- Race distribution ---
        $raceDistribution = DB::table('players')
            ->whereNull('player_id')
            ->selectRaw('race, COUNT(*) as players')
            ->groupBy('race')
            ->orderByDesc('players')
            ->get();

        // --- Win streak distribution (top 30) ---
        $streakDistribution = DB::table('player_stats')
            ->selectRaw('longest_win_streak as streak, COUNT(*) as players')
            ->groupBy('longest_win_streak')
            ->orderByDesc('longest_win_streak')
            ->limit(30)
            ->get();

        // --- Loss streak distribution ---
        $lossStreakDistribution = DB::table('rating_histories')
            ->selectRaw('player_id, result')
            ->orderBy('played_at')
            ->orderBy('id')
            ->get()
            ->groupBy('player_id')
            ->map(function ($history) {
                $maxLoss    = 0;
                $currentLoss = 0;
                foreach ($history as $h) {
                    if ($h->result !== 'win') {
                        $currentLoss++;
                        $maxLoss = max($maxLoss, $currentLoss);
                    } else {
                        $currentLoss = 0;
                    }
                }
                return $maxLoss;
            })
            ->groupBy(fn($streak) => $streak)
            ->map(fn($group) => $group->count())
            ->sortKeysDesc()
            ->take(20);

        // --- Peak rating distribution ---
        $peakRatingDistribution = DB::table('player_stats')
            ->selectRaw('FLOOR(peak_rating / 100) * 100 as bucket, COUNT(*) as players')
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        // --- Rating milestones — how many players ever reached X ---
        $milestones = [1100, 1200, 1300, 1400, 1500, 1600, 1700, 1800, 1900, 2000, 2100, 2200, 2300];
        $ratingMilestones = collect($milestones)->map(function ($rating) {
            $count = DB::table('player_stats')->where('peak_rating', '>=', $rating)->count();
            return ['rating' => $rating, 'players' => $count];
        });

        // --- Active months distribution ---
        $activeMonthsDistribution = DB::table('rating_histories')
            ->selectRaw("player_id, DATE_FORMAT(played_at, '%Y-%m') as month")
            ->groupBy('player_id', 'month')
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->count())
            ->groupBy(fn($months) => match(true) {
                $months < 3  => '1–2',
                $months < 6  => '3–5',
                $months < 12 => '6–11',
                $months < 24 => '12–23',
                $months < 36 => '24–35',
                $months < 48 => '36–47',
                $months < 60 => '48–59',
                default      => '60+',
            })
            ->map(fn($group) => $group->count())
            ->sortBy(fn($v, $k) => match($k) {
                '1–2' => 0, '3–5' => 1, '6–11' => 2, '12–23' => 3,
                '24–35' => 4, '36–47' => 5, '48–59' => 6, '60+' => 7,
            });

        // --- Head to head — how many unique opponents per player ---
        $opponentCounts = DB::table('head_to_head')
            ->selectRaw('player_a_id as player_id, COUNT(*) as opponents')
            ->groupBy('player_a_id')
            ->unionAll(
                DB::table('head_to_head')
                    ->selectRaw('player_b_id as player_id, COUNT(*) as opponents')
                    ->groupBy('player_b_id')
            )
            ->get()
            ->groupBy('player_id')
            ->map(fn($rows) => $rows->sum('opponents'))
            ->groupBy(fn($count) => match(true) {
                $count < 5   => '1–4',
                $count < 10  => '5–9',
                $count < 20  => '10–19',
                $count < 50  => '20–49',
                $count < 100 => '50–99',
                default      => '100+',
            })
            ->map(fn($group) => $group->count());

        // --- Rivalry depth — max games vs single opponent ---
        $rivalryDepth = DB::table('head_to_head')
            ->selectRaw('
                CASE
                    WHEN games_count < 5   THEN "1–4"
                    WHEN games_count < 10  THEN "5–9"
                    WHEN games_count < 20  THEN "10–19"
                    WHEN games_count < 30  THEN "20–29"
                    WHEN games_count < 50  THEN "30–49"
                    WHEN games_count < 100 THEN "50–99"
                    ELSE "100+"
                END as bucket,
                COUNT(*) as pairs,
                MIN(games_count) as sort_val
            ')
            ->groupBy('bucket')
            ->orderBy('sort_val')
            ->get();

        // --- Race matchups win rates ---
        $raceMatchups = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->whereNotIn('p1.race', ['Random', 'Unknown'])
            ->whereNotIn('p2.race', ['Random', 'Unknown'])
            ->selectRaw('p1.race as winner_race, p2.race as loser_race, COUNT(*) as games')
            ->groupBy('p1.race', 'p2.race')
            ->get();

        // --- Upset stats — how many players ever beat someone 200+ higher ---
        $upsetStats = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->selectRaw('
                SUM(CASE WHEN (rh2.rating_before - rh1.rating_before) >= 200 THEN 1 ELSE 0 END) as upsets_200,
                SUM(CASE WHEN (rh2.rating_before - rh1.rating_before) >= 300 THEN 1 ELSE 0 END) as upsets_300,
                SUM(CASE WHEN (rh2.rating_before - rh1.rating_before) >= 400 THEN 1 ELSE 0 END) as upsets_400,
                SUM(CASE WHEN (rh2.rating_before - rh1.rating_before) >= 500 THEN 1 ELSE 0 END) as upsets_500
            ')
            ->first();

        // $lastGameDate = DB::table('games')->max('date_time');

        // --- Monthly activity (games played per month) ---
        $monthlyActivity = DB::table('games')
            ->selectRaw("DATE_FORMAT(date_time, '%Y-%m') as month, COUNT(*) as games")
            ->where('date_time', '>=', \Carbon\Carbon::create(2018, 1, 1))
            ->groupByRaw("DATE_FORMAT(date_time, '%Y-%m')")
            ->orderBy('month')
            ->get();


        // --- Players by country (top 15) ---
        $playersByCountry = DB::table('players')
            ->whereNull('player_id')
            ->selectRaw('country, country_code, COUNT(*) as players')
            ->groupBy('country', 'country_code')
            ->orderByDesc('players')
            ->limit(15)
            ->get();

        // --- Glass cannon candidates — peak >= 1500, overall losses > wins ---
        $glassCannonCount = DB::table('player_stats as ps')
            ->join('player_ratings as pr', 'pr.player_id', '=', 'ps.player_id')
            ->where('ps.peak_rating', '>=', 1500)
            ->whereRaw('pr.losses > pr.wins')
            ->count();

        // --- Draw rate ---
        $drawStats = DB::table('player_ratings')
            ->selectRaw('SUM(draws) as total_draws, SUM(games_played) as total_games')
            ->first();

        return compact(
            'totalPlayers', 'totalGames',
            'gamesDistribution', 'ratingDistribution', 'gamesPerYear',
            'raceDistribution', 'streakDistribution', 'lossStreakDistribution',
            'peakRatingDistribution', 'ratingMilestones', 'activeMonthsDistribution',
            'opponentCounts', 'rivalryDepth', 'raceMatchups', 'upsetStats',
            'monthlyActivity', 'playersByCountry', 'glassCannonCount', 'drawStats'
        );
    }
}