<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlayersIncompleteController extends Controller
{
    public function __invoke()
    {
        $players = DB::table('players')
            ->whereNull('players.player_id')
            ->select('players.id', 'players.name', 'players.country', 'players.country_code', 'players.race')
            ->selectRaw('COUNT(DISTINCT g.id) as games_count')
            ->join(DB::raw('(
                SELECT id, winner_id AS player_id FROM games
                UNION ALL
                SELECT id, loser_id AS player_id FROM games
            ) g'), 'g.player_id', '=', 'players.id')
            ->where(function ($q) {
                $q->whereNull('players.race')
                  ->orWhere('players.race', 'Unknown')
                  ->orWhereNull('players.country_code')
                  ->orWhere('players.country_code', 'XX')
                  ->orWhere('players.country', 'Unknown');
            })
            ->groupBy('players.id', 'players.name', 'players.country', 'players.country_code', 'players.race')
            ->orderByDesc('games_count')
            ->get()
            ->map(function ($p) {
                $p->profile_url  = route('players.show', ['id' => $p->id, 'slug' => Str::slug($p->name)]);
                $p->missing_race    = !$p->race || $p->race === 'Unknown';
                $p->missing_country = !$p->country_code || $p->country_code === 'XX' || $p->country === 'Unknown';
                return $p;
            });

        $stats = [
            'total'           => $players->count(),
            'missing_race'    => $players->filter(fn($p) => $p->missing_race && !$p->missing_country)->count(),
            'missing_country' => $players->filter(fn($p) => $p->missing_country && !$p->missing_race)->count(),
            'missing_both'    => $players->filter(fn($p) => $p->missing_race && $p->missing_country)->count(),
        ];


        return view('dev.players-incomplete', compact('players', 'stats'));
    }
}