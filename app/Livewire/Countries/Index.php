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

    // #[Computed]
    // public function gamesAllTimeByCountry()
    // {
    //     return DB::query()
    //         ->fromSub(function ($query) {
    //             $query->selectRaw('winner_id as player_id')->from('games')
    //                 ->unionAll(DB::table('games')->selectRaw('loser_id as player_id'));
    //         }, 'all_players')
    //         ->join('players', 'players.id', '=', 'all_players.player_id')
    //         ->whereNull('players.player_id')
    //         ->whereNotIn('players.country_code', ['XX'])
    //         ->selectRaw('players.country, players.country_code, COUNT(*) as games_count')
    //         ->groupBy('players.country', 'players.country_code')
    //         ->orderByDesc('games_count')
    //         ->paginate(15);
    // }

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

    #[Computed]
    public function topCountries()
    {
        if (!$this->since || empty($this->qualifiedCountries)) return collect();

        $activePlayerIds = DB::table('rating_histories')
            ->where('played_at', '>=', $this->since)
            ->distinct()
            ->pluck('player_id');

        return DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->where('player_ratings.games_played', '>=', 15)
            ->whereIn('players.country_code', $this->qualifiedCountries)
            ->whereIn('players.id', $activePlayerIds)
            ->selectRaw('
                players.country,
                players.country_code,
                count(distinct players.id) as player_count,
                round(avg(player_ratings.rating)) as avg_rating,
                sum(player_ratings.wins) as total_wins,
                sum(player_ratings.losses) as total_losses,
                round(sum(player_ratings.wins) / (sum(player_ratings.wins) + sum(player_ratings.losses)) * 100) as win_ratio
            ')
            ->groupBy('players.country', 'players.country_code')
            ->orderByDesc('avg_rating')
            ->get();
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