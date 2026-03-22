<?php

namespace App\Livewire\Countries;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RatingHistory;
use Illuminate\Support\Facades\DB;

class Compare extends Component
{
    public string $code1;
    public string $code2;

    #[Computed]
    public function lastGameDate(): ?string
    {
        return RatingHistory::max('played_at');
    }

    #[Computed]
    public function since(): ?\Carbon\Carbon
    {
        return $this->lastGameDate ? \Carbon\Carbon::parse($this->lastGameDate)->subYear() : null;
    }

    #[Computed]
    public function country1()
    {
        return DB::table('players')
            ->where('country_code', strtoupper($this->code1))
            ->whereNull('player_id')
            ->value('country') ?? strtoupper($this->code1);
    }

    #[Computed]
    public function country2()
    {
        return DB::table('players')
            ->where('country_code', strtoupper($this->code2))
            ->whereNull('player_id')
            ->value('country') ?? strtoupper($this->code2);
    }

    #[Computed]
    public function h2h()
    {
        $c1 = strtoupper($this->code1);
        $c2 = strtoupper($this->code2);

        $c1wins = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->where('p1.country_code', $c1)
            ->where('p2.country_code', $c2)
            ->count();

        $c2wins = DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->where('p1.country_code', $c2)
            ->where('p2.country_code', $c1)
            ->count();

        return [
            'c1_wins' => $c1wins,
            'c2_wins' => $c2wins,
            'total' => $c1wins + $c2wins,
        ];
    }

    #[Computed]
    public function recentGames()
    {
        $c1 = strtoupper($this->code1);
        $c2 = strtoupper($this->code2);

        return RatingHistory::where('result', 'win')
            ->with('game.winner', 'game.loser')
            ->whereHas('game.winner', fn($q) => $q->whereIn('country_code', [$c1, $c2]))
            ->whereHas('game.loser', fn($q) => $q->whereIn('country_code', [$c1, $c2]))
            ->whereHas('game', function ($q) use ($c1, $c2) {
                $q->whereHas('winner', fn($w) => $w->where('country_code', $c1))
                  ->whereHas('loser', fn($l) => $l->where('country_code', $c2));
            })
            ->orWhere(function ($q) use ($c1, $c2) {
                $q->where('result', 'win')
                  ->whereHas('game', function ($gq) use ($c1, $c2) {
                      $gq->whereHas('winner', fn($w) => $w->where('country_code', $c2))
                        ->whereHas('loser', fn($l) => $l->where('country_code', $c1));
                  });
            })
            ->orderByDesc('played_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function topPlayersCountry1()
    {
        $since = $this->since;
        if (!$since) return collect();

        $activeIds = DB::table('rating_histories')
        ->where('played_at', '>=', $since)
        ->distinct()->pluck('player_id');

    return DB::table('players')
        ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
        ->whereNull('players.player_id')
        ->where('players.country_code', strtoupper($this->code1))
        ->where('player_ratings.games_played', '>=', 15)
        ->whereIn('players.id', $activeIds)
        ->selectRaw('players.id, players.name, players.race, players.country_code, player_ratings.rating, player_ratings.games_played, player_ratings.wins, player_ratings.losses')
        ->orderByDesc('player_ratings.rating')
        ->limit(10)
        ->get();

    }

    #[Computed]
    public function topPlayersCountry2()
    {
        $since = $this->since;
        if (!$since) return collect();
        $activeIds = DB::table('rating_histories')
        ->where('played_at', '>=', $since)
        ->distinct()->pluck('player_id');

        return DB::table('players')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
            ->whereNull('players.player_id')
            ->where('players.country_code', strtoupper($this->code2))
            ->where('player_ratings.games_played', '>=', 15)
            ->whereIn('players.id', $activeIds)
            ->selectRaw('players.id, players.name, players.race, players.country_code, player_ratings.rating, player_ratings.games_played, player_ratings.wins, player_ratings.losses')
            ->orderByDesc('player_ratings.rating')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function raceMatchups()
    {
        $c1 = strtoupper($this->code1);
        $c2 = strtoupper($this->code2);

        return DB::table('rating_histories as rh1')
            ->join('rating_histories as rh2', function ($join) {
                $join->on('rh1.game_id', '=', 'rh2.game_id')
                     ->where('rh1.result', '=', 'win')
                     ->where('rh2.result', '=', 'loss');
            })
            ->join('players as p1', 'p1.id', '=', 'rh1.player_id')
            ->join('players as p2', 'p2.id', '=', 'rh2.player_id')
            ->where(function ($q) use ($c1, $c2) {
                $q->where(function ($q2) use ($c1, $c2) {
                    $q2->where('p1.country_code', $c1)->where('p2.country_code', $c2);
                })->orWhere(function ($q2) use ($c1, $c2) {
                    $q2->where('p1.country_code', $c2)->where('p2.country_code', $c1);
                });
            })
            ->whereNotIn('p1.race', ['Random', 'Unknown'])
            ->whereNotIn('p2.race', ['Random', 'Unknown'])
            ->selectRaw('p1.race as winner_race, p1.country_code as winner_country, p2.race as loser_race, count(*) as games')
            ->groupBy('p1.race', 'p1.country_code', 'p2.race')
            ->get();
    }

    public function render()
    {
        return view('livewire.countries.compare');
    }
}