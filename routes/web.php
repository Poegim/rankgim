<?php

use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/rankings', function () {
    return view('rankings');
})->name('rankings.index');

use Illuminate\Support\Str;

Route::get('/players/{id}-{slug}', function ($id) {
    return view('players.show', ['playerId' => $id]);
})->name('players.show');

Route::get('/about', function () {
    return view('about', [
        'totalGames' => \App\Models\Game::count(),
        'firstGame'  => \App\Models\Game::min('date_time'),
        'lastGame'   => \App\Models\Game::max('date_time'),
        'totalPlayers' => \App\Models\PlayerRating::count(),
    ]);
})->name('about');

require __DIR__.'/settings.php';
