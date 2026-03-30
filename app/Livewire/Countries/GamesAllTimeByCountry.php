<?php

namespace App\Livewire\Countries;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class GamesAllTimeByCountry extends Component
{

    use WithPagination;

    /**
     * This component calculates the total number of games played by country, regardless of the year.
     * It does this by creating a subquery that combines all winner and loser player IDs from the games table,
     * then joins this with the players table to get the country information, and finally groups and counts the games by country.
     */
    #[Computed]
    public function gamesAllTimeByCountry()
    {
        return DB::query()
            ->fromSub(function ($query) {
                $query->selectRaw('winner_id as player_id')->from('games')
                    ->unionAll(DB::table('games')->selectRaw('loser_id as player_id'));
            }, 'all_players')
            ->join('players', 'players.id', '=', 'all_players.player_id')
            ->whereNull('players.player_id')
            ->whereNotIn('players.country_code', ['XX'])
            ->selectRaw('players.country, players.country_code, COUNT(*) as games_count')
            ->groupBy('players.country', 'players.country_code')
            ->orderByDesc('games_count')
            ->simplePaginate(15);
    }
    
    public function render()
    {
        return view('livewire.countries.games-all-time-by-country');
    }
}
