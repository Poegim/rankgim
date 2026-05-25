<?php

namespace App\Console\Commands;

use App\Services\Soop\SoopLiveStatusService;
use Illuminate\Console\Command;
use Throwable;

class RefreshSoopStreams extends Command
{
    protected $signature   = 'soop:refresh-streams';
    protected $description = 'Fetch live BW broadcasts from SOOP and refresh cache';

    public function handle(SoopLiveStatusService $service): int
    {
        try {
            $count = $service->refresh();
            $this->info("SOOP refresh ok — {$count} broadcasts cached.");
            return self::SUCCESS;
        } catch (Throwable $e) {
            // We log inside the client; here we just surface it to the
            // scheduler. Returning FAILURE causes Laravel to mark the run
            // as failed in the schedule monitor (if enabled).
            $this->error('SOOP refresh failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}