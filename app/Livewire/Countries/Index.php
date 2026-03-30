<?php

namespace App\Livewire\Countries;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RatingHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public string $yearFilter = 'last12';

    public string $compareCountry1 = '';
    public string $compareCountry2 = '';

    public function goCompare(): void
    {
        if (!$this->compareCountry1 || !$this->compareCountry2) return;
        if ($this->compareCountry1 === $this->compareCountry2) return;
        
        $this->redirect(
            route('countries.compare', [
                'code1' => strtolower($this->compareCountry1),
                'code2' => strtolower($this->compareCountry2),
            ]),
            navigate: true
        );
    }

    #[Computed]
    public function allCountries()
    {
        return DB::table('players')
            ->whereNull('player_id')
            ->whereNotIn('country_code', ['XX'])
            ->selectRaw('country, country_code')
            ->groupBy('country', 'country_code')
            ->orderBy('country')
            ->get();
    }
    
    #[Computed]
    public function availableYears(): array
    {
        $lastYear = (int) DB::table('games')->selectRaw('YEAR(MAX(date_time)) as y')->value('y');
        $firstYear = (int) DB::table('games')->selectRaw('YEAR(MIN(date_time)) as y')->value('y');
        $years = ['last12'];
        for ($y = $lastYear - 1; $y >= $firstYear; $y--) {
            $years[] = (string) $y;
        }
        return $years;
    }


    #[Computed]
    public function gamesYearlyByCountry()
    {
        if ($this->yearFilter === 'last12') {
            if (!$this->since) return collect();
            $from = $this->since;
            $to = Carbon::parse($this->lastGameDate);
        } else {
            $from = Carbon::create((int) $this->yearFilter, 1, 1);
            $to = Carbon::create((int) $this->yearFilter, 12, 31, 23, 59, 59);
        }

        return DB::query()
            ->fromSub(function ($query) use ($from, $to) {
                $query->selectRaw('winner_id as player_id')
                    ->from('games')
                    ->whereBetween('date_time', [$from, $to])
                    ->unionAll(
                        DB::table('games')
                            ->selectRaw('loser_id as player_id')
                            ->whereBetween('date_time', [$from, $to])
                    );
            }, 'all_players')
            ->join('players', 'players.id', '=', 'all_players.player_id')
            ->whereNull('players.player_id')
            ->whereNotIn('players.country_code', ['XX'])
            ->selectRaw('players.country, players.country_code, COUNT(*) as games_count')
            ->groupBy('players.country', 'players.country_code')
            ->orderByDesc('games_count')
            ->limit(15)
            ->get();
    }

    public function setYearFilter(string $year): void
    {
        $this->yearFilter = $year;
        unset($this->gamesYearlyByCountry);
    }

    #[Computed]
    public function lastGameDate(): ?string
    {
        return RatingHistory::max('played_at');
    }

    #[Computed]
    public function since(): ?Carbon
    {
        return $this->lastGameDate ? Carbon::parse($this->lastGameDate)->subYear() : null;
    }

    #[Computed]
    public function qualifiedCountries()
    {
        if (!$this->since) return [];

        return DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->join('rating_histories', 'rating_histories.player_id', '=', 'players.id')
            ->where('player_ratings.games_played', '>=', 15)
            ->where('rating_histories.played_at', '>=', $this->since)
            ->whereNotIn('players.country_code', ['XX'])
            ->selectRaw('players.country, players.country_code, count(distinct players.id) as player_count')
            ->groupBy('players.country', 'players.country_code')
            ->having('player_count', '>=', 5)
            ->orderByDesc('player_count')
            ->limit(10)
            ->pluck('country_code')
            ->toArray();
    }

    /** 
     * This method calculates the top countries based on the average rating of their top 5 players, 
     * but only includes countries that have at least 5 active players in the last 12 months. 
     * It first retrieves all games played in the last 12 months along with player and rating information, 
     * then aggregates this data to calculate wins/losses per player, 
     * groups players by country, filters for qualified countries, 
     * and finally calculates the average rating and win ratio for the top 5 players of each qualified country.
     */
    #[Computed]
    public function topCountries()
    {
        if (!$this->since) return collect();

        // 1. Get all games from last 12 months with player info
        $games = DB::table('rating_histories')
            ->join('players', 'players.id', '=', 'rating_histories.player_id')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->whereNull('players.player_id')
            ->where('player_ratings.games_played', '>=', 15)
            ->where('rating_histories.played_at', '>=', $this->since)
            ->whereNotIn('players.country_code', ['XX'])
            ->select(
                'players.id',
                'players.country',
                'players.country_code',
                'player_ratings.rating',
                'rating_histories.result'
            )
            ->get();

        // 2. Aggregate per player
        $players = $games->groupBy('id')->map(function ($rows) {
            $first = $rows->first();
            return (object) [
                'id' => $first->id,
                'country' => $first->country,
                'country_code' => $first->country_code,
                'rating' => $first->rating,
                'wins' => $rows->where('result', 'win')->count(),
                'losses' => $rows->where('result', 'loss')->count(),
            ];
        });

        // 3. Group by country
        $grouped = $players->groupBy('country_code');

        // 4. Filter countries with >= 5 players
        $qualified = $grouped->filter(fn($group) => $group->count() >= 5);

        // 5. Build stats: player_count = real count, stats from top 5
        return $qualified->map(function ($players) {
            $sorted = $players->sortByDesc('rating');
            $top5 = $sorted->take(5);
            
            // Wins/losses from ALL players of this country
            $totalWins = $players->sum('wins');
            $totalLosses = $players->sum('losses');

            return (object) [
                'country' => $players->first()->country,
                'country_code' => $players->first()->country_code,
                'player_count' => $players->count(),
                'avg_rating' => round($top5->avg('rating')),
                'total_wins' => $totalWins,
                'total_losses' => $totalLosses,
                'win_ratio' => ($totalWins + $totalLosses) > 0 ? round($totalWins / ($totalWins + $totalLosses) * 100) : 0,
            ];
        })->sortByDesc('avg_rating')->values();
    }

    #[Computed]
    public function countryMatchups()
    {
        if (!$this->since || empty($this->qualifiedCountries)) return collect();

        return DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->whereIn('p1.country_code', $this->qualifiedCountries)
            ->whereIn('p2.country_code', $this->qualifiedCountries)
            ->where('p1.country_code', '!=', 'p2.country_code')
            ->where('rh1.played_at', '>=', $this->since)
            ->selectRaw('p1.country_code as winner_country, p2.country_code as loser_country, count(*) as games')
            ->groupBy('p1.country_code', 'p2.country_code')
            ->get();
    }

    public function render()
    {
        return view('livewire.countries.index');
    }
}