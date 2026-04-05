<?php

namespace App\Console\Commands;

use App\Services\EloService;
use Illuminate\Console\Command;

class RecalculateElo extends Command
{
    protected $signature = 'elo:recalculate';
    protected $description = 'Recalculate all ELO ratings from scratch';

    public function handle(EloService $elo): void
    {
        $this->info('Starting ELO recalculation...');
        $elo->recalculateAll();
        $this->info('Done!');
    }
}