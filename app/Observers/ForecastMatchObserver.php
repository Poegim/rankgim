<?php

namespace App\Observers;

use App\Models\ForecastMatch;

class ForecastMatchObserver
{
    // Refund all pending predictions when a match is soft-deleted
    public function deleting(ForecastMatch $match): void
    {
        $match->predictions()
            ->where('result', 'pending')
            ->with('wallet')
            ->each(function ($prediction) {
                // Return stake to wallet
                $prediction->wallet->increment('balance', $prediction->stake);

                // Mark prediction as refunded
                $prediction->update([
                    'result'      => 'refunded',
                    'refunded_at' => now(),
                ]);
            });
    }
}