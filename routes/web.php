<?php

use App\Http\Controllers\HistoryController;
use App\Livewire\AchievementsBrowser;
use App\Livewire\Countries\Compare as CountriesCompare;
use App\Livewire\Countries\Index;
use App\Livewire\Players\Compare;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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


// Stats route (previously History)
Route::get('/stats', [HistoryController::class, 'index'])->name('stats.index');


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

// Achievements browser
Route::get('/achievements', AchievementsBrowser::class)->name('achievements.index');

// Admin routes with middleware for authentication and admin access
Route::middleware(['auth', 'verified',  App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => view('admin.index'))->name('index');
    Route::get('/achievement-insights', \App\Livewire\Admin\AchievementInsights::class)->name('achievement-insights');



});

// TEMP: List all main players (not aliases) with game count — for duplicate detection
Route::get('/dev/players-for-dedup', function () {
    $players = \App\Models\Player::whereNull('players.player_id')
        ->select('players.id', 'players.name', 'players.country', 'players.country_code', 'players.race')
        ->selectRaw('COUNT(DISTINCT g.id) as games_count')
        ->join(DB::raw('(
            SELECT id, winner_id AS player_id FROM games
            UNION ALL
            SELECT id, loser_id AS player_id FROM games
        ) g'), 'g.player_id', '=', 'players.id')
        ->groupBy('players.id', 'players.name', 'players.country', 'players.country_code', 'players.race')
        ->orderBy('players.name')
        ->get();

    return response()->json([
        'count'   => $players->count(),
        'players' => $players,
    ]);
});




require __DIR__.'/settings.php';
