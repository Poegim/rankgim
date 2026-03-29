<?php

namespace App\Livewire\Countries;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class GamesAllTimeByCountry extends Component
{

    use WithPagination;

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
