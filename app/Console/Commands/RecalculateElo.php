<?php

namespace App\Console\Commands;

use App\Services\EloService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecalculateElo extends Command
{
    protected $signature = 'elo:recalculate';
    protected $description = 'Recalculate all ELO ratings from scratch';

    public function handle(EloService $elo): void
    {
        
        $this->info('Starting ELO recalculation...');
        $elo->recalculateAll();
        // After recalculation completes:
        Cache::forget('dashboard.spread_chart');
        $this->info('Cleared spread chart cache.');
        Cache::forget('dashboard.top_players');
        $this->info('Cleared top players cache.');
        Cache::forget('dashboard.race_matchups');
        $this->info('Cleared race matchups cache.');
        $this->info('Done!');
    }
}