<?php

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
Route::get('/players/{id}-{slug}', function ($id) {
    return view('players.show', ['playerId' => $id]);
})->name('players.show');

// Tournament routes
Route::get('/tournaments', fn() => view('tournaments.index'))->name('tournaments.index');
Route::get('/tournaments/{id}', fn($id) => view('tournaments.show', ['tournamentId' => $id]))->name('tournaments.show');


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

// Admin routes with middleware protection
Route::middleware(['auth', App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', fn() => view('admin.users'))->name('users');
});

require __DIR__.'/settings.php';
