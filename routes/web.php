<?php

use App\Livewire\Countries\Index;
use App\Livewire\Players\Compare;
use App\Livewire\Countries\Compare as CountriesCompare;
use Illuminate\Support\Facades\Route;

// Dashboard route
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Rankings route
Route::get('/rankings', function () {
    return view('rankings');
    })->name('rankings.index');
    
// Player profile route with slug for SEO-friendly URLs
Route::get('/players/{id}-{slug}', function ($id, $slug) {
    return view('players.show', ['playerId' => $id]);
})->name('players.show');

// Players route
Route::get('/players', fn() => view('players.index'))->name('players.index');
// Player comparison search route
Route::get('/compare', fn() => view('players.compare-search'))->name('players.compare-search');
// Player comparison route with two player IDs in the URL
Route::get('/compare/{id1}-vs-{id2}', fn($id1, $id2) => view('players.compare', ['id1' => $id1, 'id2' => $id2]))->name('players.compare');

// Tournament routes
Route::get('/tournaments', fn() => view('tournaments.index'))->name('tournaments.index');
Route::get('/tournaments/{id}', fn($id) => view('tournaments.show', ['tournamentId' => $id]))->name('tournaments.show');

// Game creation route with middleware for authentication and tournament management permissions
Route::middleware(['auth', 'verified',  App\Http\Middleware\EnsureUserCanManageGames::class])->group(function () {
    Route::get('/tournaments/{id}/games/import', fn($id) => view('games.import', ['tournamentId' => $id]))->name('games.import');
});

// Countries route
Route::get('/countries', Index::class)->name('countries.index');
Route::get('/countries/{code1}-vs-{code2}', CountriesCompare::class)->name('countries.compare');

Route::get('/test-countries2', function () {
    $component = new \App\Livewire\Countries\Index();
    return [
        'qualified' => $component->qualifiedCountries,
        'topCountries' => $component->topCountries,
    ];
});

Route::get('/test-countries', function () {
    $lastGame = \App\Models\RatingHistory::max('played_at');
    $since = \Carbon\Carbon::parse($lastGame)->subYear();
    
    $result = DB::table('players')
        ->join('player_ratings', 'player_ratings.player_id', '=', 'players.id')
        ->join('rating_histories', 'rating_histories.player_id', '=', 'players.id')
        ->where('player_ratings.games_played', '>=', 15)
        ->where('rating_histories.played_at', '>=', $since)
        ->whereNotIn('players.country_code', ['XX'])
        ->selectRaw('players.country_code, count(distinct players.id) as player_count')
        ->groupBy('players.country_code')
        ->having('player_count', '>=', 15)
        ->orderByDesc('player_count')
        ->get();
    
    return $result;
});

// Games route
Route::get('/games', fn() => view('games.index'))->name('games.index');

// About page with statistics
Route::get('/about', function () {
    return view('about', [
        'totalGames' => \App\Models\Game::count(),
        'firstGame'  => \App\Models\Game::min('date_time'),
        'lastGame'   => \App\Models\Game::max('date_time'),
        'totalPlayers' => \App\Models\PlayerRating::count(),
    ]);
})->name('about');

// Events route using Livewire component
Route::get('/events', App\Livewire\Events\Index::class)->name('events.index');

// Admin routes with middleware for authentication and admin access
Route::middleware(['auth', 'verified',  App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => view('admin.index'))->name('index');
});

require __DIR__.'/settings.php';
