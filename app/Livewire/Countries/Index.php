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
    public function topCountries()
    {
        return \App\Models\CountryStat::orderByDesc('avg_rating')->get();
    }

    #[Computed]
    public function countryMatchups()
    {
        return DB::table('country_matchups')->get();
    }

    public function render()
    {
        return view('livewire.countries.index');
    }
}