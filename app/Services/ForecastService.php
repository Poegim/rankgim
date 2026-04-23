<?php

namespace App\Services;

use App\Models\ForecastMatch;
use App\Models\ForecastPrediction;
use App\Models\ForecastSeason;
use App\Models\ForecastSeasonSnapshot;
use App\Models\ForecastWallet;
use App\Models\Player;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    // ── Wallet ────────────────────────────────────────

    // Get or create a wallet for the user in the active season
    public function getOrCreateWallet(User $user, string $currency): ForecastWallet
    {
        $season = ForecastSeason::current();

        return ForecastWallet::firstOrCreate(
            [
                'user_id'   => $user->id,
                'season_id' => $season->id,
            ],
            [
                'currency' => $currency,
                'balance'  => ForecastWallet::STARTING_BALANCE,
            ]
        );
    }

    // Reset wallet balance back to starting amount if eligible
    public function resetWallet(ForecastWallet $wallet): bool
    {
        if (! $wallet->canReset()) {
            return false;
        }

        $wallet->reset();

        return true;
    }

    // ── Betting — foreigner matches (player FK) ───────

    // Place a prediction on a foreigner match by picking a player
    public function placeBet(
        User $user,
        ForecastMatch $match,
        Player $pickedPlayer,
        float $stake,
    ): ForecastPrediction {
        return DB::transaction(function () use ($user, $match, $pickedPlayer, $stake) {
            $wallet = $this->lockWallet($user, $match->season_id);

            $this->validateBet($match, $wallet, $stake);

            abort_if(
                ! in_array($pickedPlayer->id, [$match->player_a_id, $match->player_b_id]),
                422,
                'Picked player is not part of this match.'
            );

            $isPickingA  = $pickedPlayer->id === $match->player_a_id;
            $pickedOdds  = $isPickingA ? (float) $match->odds_a : (float) $match->odds_b;
            $otherOdds   = $isPickingA ? (float) $match->odds_b : (float) $match->odds_a;
            $pickedRace  = $isPickingA ? $match->player_a_race : $match->player_b_race;

            // Apply flat multiplier
            $pickedOdds *= (float) $match->multiplier;
            $otherOdds  *= (float) $match->multiplier;

            $bonusMultiplier = ForecastPrediction::resolveBonusMultiplier(
                currency:   $wallet->currency,
                pickedRace: $pickedRace,
                pickedOdds: $pickedOdds,
                otherOdds:  $otherOdds,
            );

            $potentialPayout = round($stake * $pickedOdds * $bonusMultiplier, 2);

            // Deduct stake from wallet immediately
            $wallet->decrement('balance', $stake);

            return ForecastPrediction::create([
                'user_id'          => $user->id,
                'match_id'         => $match->id,
                'wallet_id'        => $wallet->id,
                'pick_player_id'   => $pickedPlayer->id,
                'stake'            => $stake,
                'odds_at_time'     => $pickedOdds,
                'bonus_multiplier' => $bonusMultiplier,
                'potential_payout' => $potentialPayout,
                'result'           => 'pending',
            ]);
        });
    }

    // ── Betting — non-foreigner matches (side a/b) ────

    // Place a prediction on a korean/clan/national match by picking side a or b
    public function placeBetBySide(
        User $user,
        ForecastMatch $match,
        string $side, // 'a' or 'b'
        float $stake,
    ): ForecastPrediction {
        return DB::transaction(function () use ($user, $match, $side, $stake) {
            $wallet = $this->lockWallet($user, $match->season_id);

            $this->validateBet($match, $wallet, $stake);

            $isPickingA  = $side === 'a';
            $pickedOdds  = $isPickingA ? (float) $match->odds_a : (float) $match->odds_b;
            $otherOdds   = $isPickingA ? (float) $match->odds_b : (float) $match->odds_a;
            $pickedRace  = $isPickingA ? $match->player_a_race : $match->player_b_race;

            // Apply flat multiplier
            $pickedOdds *= (float) $match->multiplier;
            $otherOdds  *= (float) $match->multiplier;

            $bonusMultiplier = ForecastPrediction::resolveBonusMultiplier(
                currency:   $wallet->currency,
                pickedRace: $pickedRace,
                pickedOdds: $pickedOdds,
                otherOdds:  $otherOdds,
            );

            $potentialPayout = round($stake * $pickedOdds * $bonusMultiplier, 2);

            $wallet->decrement('balance', $stake);

            return ForecastPrediction::create([
                'user_id'          => $user->id,
                'match_id'         => $match->id,
                'wallet_id'        => $wallet->id,
                'pick_player_id'   => null, // no FK for non-foreigner matches
                'pick_side'        => $side, // store side directly
                'stake'            => $stake,
                'odds_at_time'     => $pickedOdds,
                'bonus_multiplier' => $bonusMultiplier,
                'potential_payout' => $potentialPayout,
                'result'           => 'pending',
            ]);
        });
    }

    // ── Settlement — foreigner matches ────────────────

    // Settle a foreigner match by picking the winning player
    public function settleMatch(ForecastMatch $match, Player $winner, User $settledBy): void
    {
        abort_if($match->isSettled(), 422, 'Match is already settled.');
        abort_if(
            ! in_array($winner->id, [$match->player_a_id, $match->player_b_id]),
            422,
            'Winner must be one of the two players in this match.'
        );

        DB::transaction(function () use ($match, $winner, $settledBy) {
            $match->update([
                'winner_id'  => $winner->id,
                'settled_at' => now(),
                'settled_by' => $settledBy->id,
            ]);

            $this->payoutPredictions($match, fn($prediction) =>
                $prediction->pick_player_id === $winner->id
            );
        });
    }

    // ── Settlement — non-foreigner matches ────────────

    // Settle a korean/clan/national match by declaring winner side a or b
    public function settleMatchBySide(ForecastMatch $match, string $winnerSide, User $settledBy): void
    {
        abort_if($match->isSettled(), 422, 'Match is already settled.');
        abort_if(! in_array($winnerSide, ['a', 'b']), 422, 'Winner side must be a or b.');

        DB::transaction(function () use ($match, $winnerSide, $settledBy) {
            $match->update([
                'winner_side' => $winnerSide,
                'settled_at'  => now(),
                'settled_by'  => $settledBy->id,
            ]);

            $this->payoutPredictions($match, fn($prediction) =>
                $prediction->pick_side === $winnerSide
            );
        });
    }

    // ── Season ────────────────────────────────────────

    // Close the active season — save snapshots and mark as inactive
    public function closeSeason(ForecastSeason $season): void
    {
        abort_if(! $season->is_active, 422, 'Season is already closed.');

        DB::transaction(function () use ($season) {
            $wallets = ForecastWallet::where('season_id', $season->id)
                ->whereHas('predictions', fn($q) => $q->whereIn('result', ['won', 'lost']))
                ->with('predictions')
                ->get()
                ->map(function ($wallet) {
                    $settled = $wallet->predictions->whereIn('result', ['won', 'lost']);

                    $wallet->computed_profit = $settled->sum('actual_payout') - $settled->sum('stake');
                    $wallet->total           = $settled->count();
                    $wallet->correct         = $settled->where('result', 'won')->count();

                    return $wallet;
                })
                ->sortByDesc('computed_profit')
                ->values();

            $wallets->each(function ($wallet, $index) use ($season) {
                ForecastSeasonSnapshot::create([
                    'season_id'           => $season->id,
                    'user_id'             => $wallet->user_id,
                    'currency'            => $wallet->currency,
                    'final_profit'        => $wallet->computed_profit,
                    'final_balance'       => $wallet->balance,
                    'total_predictions'   => $wallet->total,
                    'correct_predictions' => $wallet->correct,
                    'rank'                => $index + 1,
                ]);
            });

            $season->update([
                'is_active' => false,
                'ends_at'   => now(),
            ]);
        });
    }

    // ── Private helpers ───────────────────────────────

    // Lock wallet row for update and return it
    private function lockWallet(User $user, int $seasonId): ForecastWallet
    {
        return ForecastWallet::where('user_id', $user->id)
            ->where('season_id', $seasonId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    // Shared bet validation logic
    private function validateBet(ForecastMatch $match, ForecastWallet $wallet, float $stake): void
    {
        abort_if(! $match->isOpen(), 422, 'Betting is closed for this match.');

        abort_if(
            ForecastPrediction::where('user_id', $wallet->user_id)
                ->where('match_id', $match->id)
                ->exists(),
            422,
            'You have already placed a prediction on this match.'
        );

        abort_if($stake < 1, 422, 'Minimum stake is 1.');
        abort_if($stake > $wallet->balance, 422, 'Insufficient balance.');
    }

    // Pay out winning predictions and mark losers
    private function payoutPredictions(ForecastMatch $match, callable $isWinner): void
    {
        $match->predictions()
            ->where('result', 'pending')
            ->with('wallet')
            ->each(function ($prediction) use ($isWinner) {
                $won = $isWinner($prediction);

                if ($won) {
                    $payout = $prediction->potential_payout;
                    $prediction->wallet->increment('balance', $payout);
                }

                $prediction->update([
                    'result'        => $won ? 'won' : 'lost',
                    'actual_payout' => $won ? $prediction->potential_payout : null,
                ]);
            });
    }
}