<?php

namespace Database\Seeders;

use App\Models\ForecastPrediction;
use App\Models\ForecastWallet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Wipe only the data created by FakeForecastSeeder.
 *
 * Matches the fake users purely by the @test.local email domain, so real
 * accounts stay untouched. Cascades through wallets & predictions.
 *
 * Run: sail artisan db:seed --class=FakeForecastSeederWipe
 */
class FakeForecastSeederWipe extends Seeder
{
    public function run(): void
    {
        $fakeUserIds = User::where('email', 'like', '%@test.test')->pluck('id');

        if ($fakeUserIds->isEmpty()) {
            $this->command->info('No fake users found — nothing to wipe.');
            return;
        }

        DB::transaction(function () use ($fakeUserIds) {
            // Predictions first (they reference users & wallets)
            $predictionCount = ForecastPrediction::whereIn('user_id', $fakeUserIds)->count();
            ForecastPrediction::whereIn('user_id', $fakeUserIds)->delete();

            // Wallets next
            $walletCount = ForecastWallet::whereIn('user_id', $fakeUserIds)->count();
            ForecastWallet::whereIn('user_id', $fakeUserIds)->delete();

            // Users last
            $userCount = User::whereIn('id', $fakeUserIds)->count();
            User::whereIn('id', $fakeUserIds)->delete();

            $this->command->info("Wiped {$userCount} fake users, {$walletCount} wallets, {$predictionCount} predictions.");
        });

        // Note: matches are NOT deleted here — they aren't tied to fake users.
        // If you want to clear matches too, do it manually:
        //   sail artisan tinker
        //   >>> \App\Models\ForecastMatch::where('season_id', \App\Models\ForecastSeason::current()->id)->forceDelete();
        $this->command->line('Matches were not touched. Delete them manually if needed.');
    }
}