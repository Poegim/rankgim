<?php

namespace App\Console\Commands;

use App\Services\EloService;
use App\Services\RecalculationReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RecalculateElo extends Command
{
    protected $signature = 'elo:recalculate';
    protected $description = 'Recalculate all ELO ratings from scratch';

    public function handle(EloService $elo, RecalculationReportService $reports): void
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

        // Generate the recalculation report. Must run AFTER recalculateAll() because
        // it relies on fresh player_ratings and the new monthly rating_snapshots row.
        $this->info('Generating recalculation report...');
        $report = $reports->generate();

        if ($report->articles()->exists()) {
            $this->info(sprintf(
                'Report saved with article (id=%d, %d games added across %d tournaments).',
                $report->id,
                $report->summary['totals']['games_added'] ?? 0,
                $report->summary['totals']['tournaments_touched'] ?? 0,
            ));
        } else {
            $this->info(sprintf(
                'Report saved without article (id=%d, no meaningful changes).',
                $report->id,
            ));
        }

        $this->info('Done!');
    }
}