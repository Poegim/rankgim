<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PlayersIncompleteController;
use App\Livewire\AchievementsBrowser;
use App\Livewire\Countries\Compare as CountriesCompare;
use App\Livewire\Countries\Index;
use Illuminate\Support\Facades\DB;
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
// News & updates routes — order matters: literal segments before {slug} placeholders
Route::get('/news', fn() => view('articles.index'))->name('articles.index');

// Admin-only article CRUD (must come before /news/{slug} so /news/create matches first)
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
    Route::get('/news/create', fn() => view('articles.create'))->name('articles.create');
    Route::get('/news/{slug}/edit', function (string $slug) {
        $article = \App\Models\Article::where('slug', $slug)->firstOrFail();
        return view('articles.edit', ['article' => $article]);
    })->name('articles.edit');
    Route::delete('/news/{slug}', [ArticleController::class, 'destroy'])->name('articles.destroy');
});

// Public article view (after admin routes so /news/create is matched first)
Route::get('/news/{slug}', [ArticleController::class, 'show'])->name('articles.show');

// Events route using Livewire component
Route::get('/events', App\Livewire\Events\Index::class)->name('events.index');

// Achievements browser
Route::get('/achievements', AchievementsBrowser::class)->name('achievements.index');

// Forecasting routes
Route::get('/forecast', App\Livewire\Forecast\Index::class)->name('forecast.index');


// Admin routes with middleware for authentication and admin access
Route::middleware(['auth', 'verified',  App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    // Default landing — Users management
    Route::get('/', fn() => view('admin.users'))->name('index');

    // Streams tab — component lives in App\Livewire\Admin\StreamList (built separately)
    Route::get('/streams', fn() => view('admin.streams'))->name('streams');

    // Inactive players tab
    Route::get('/inactive-players', fn() => view('admin.inactive-players'))->name('inactive-players');

    // Subpage linked from the Users tab
    Route::get('/achievement-insights', \App\Livewire\Admin\AchievementInsights::class)->name('achievement-insights');

    Route::get('/soop-streamers', \App\Livewire\Admin\SoopStreamers::class)
        ->name('soop-streamers');

});

// TEMP: Players missing race or country — for community cleanup
Route::get('/dev/players-incomplete', PlayersIncompleteController::class)->name('dev.players-incomplete');

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
