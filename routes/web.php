<?php

use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $players = Player::inRandomOrder()->limit(5)->get();
    $tournaments = Tournament::orderBy('id', 'desc')->limit(5)->get();

    return view('dashboard', [
        'players' => $players,
        'tournaments' => $tournaments
    ]);
})->name('dashboard');

Route::get('/rankings', function () {
    return view('rankings');
})->name('rankings.index');

require __DIR__.'/settings.php';
