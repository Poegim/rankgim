<?php

namespace Database\Seeders;

use App\Models\ForecastMatch;
use App\Models\ForecastPrediction;
use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/**
 * Fake forecast seeder — populates an existing season with test data.
 *
 * What it creates:
 *   - 8 fake users (keeps existing users intact)
 *   - 1 wallet per user, spread across all 4 currencies
 *   - ~15 matches covering every match_type (foreigner / korean / clan / national)
 *     and every lifecycle state (open / locked-not-settled / settled)
 *   - Predictions from multiple users per match — drives the crowd %, leaderboard
 *     spread, and the won/lost/pending color variety on the cards.
 *
 * Run:   sail artisan db:seed --class=FakeForecastSeeder
 * Wipe:  sail artisan db:seed --class=FakeForecastSeeder -- --wipe  (see bottom)
 */
class FakeForecastSeeder extends Seeder
{
    // Preset test users — short emails so you can log in quickly.
    // Password for all: "password"
    // Currency split is even: 2 users per currency (4 currencies = 8 users).
    private const FAKE_USERS = [
        ['name' => 'Test User 1', 'email' => 'test1@test.test', 'currency' => 'minerals'],
        ['name' => 'Test User 2', 'email' => 'test2@test.test', 'currency' => 'minerals'],
        ['name' => 'Test User 3', 'email' => 'test3@test.test', 'currency' => 'khaydarin'],
        ['name' => 'Test User 4', 'email' => 'test4@test.test', 'currency' => 'khaydarin'],
        ['name' => 'Test User 5', 'email' => 'test5@test.test', 'currency' => 'biomass'],
        ['name' => 'Test User 6', 'email' => 'test6@test.test', 'currency' => 'biomass'],
        ['name' => 'Test User 7', 'email' => 'test7@test.test', 'currency' => 'credits'],
        ['name' => 'Test User 8', 'email' => 'test8@test.test', 'currency' => 'credits'],
    ];

    public function run(): void
    {
        $season = ForecastSeason::current();

        if (! $season) {
            $this->command->error('No active season found. Start a season first from the Forecast page.');
            return;
        }

        $this->command->info("Seeding fake data into season: {$season->name}");

        // 1. Users + wallets --------------------------------------------------
        $users = $this->createUsersAndWallets($season);
        $this->command->info('✓ ' . count($users) . ' users with wallets ready');

        // 2. Matches ----------------------------------------------------------
        $matches = $this->createMatches($season);
        $this->command->info('✓ ' . count($matches) . ' matches created');

        // 3. Predictions ------------------------------------------------------
        $predictionCount = $this->createPredictions($matches, $users);
        $this->command->info("✓ {$predictionCount} predictions placed");

        // 4. Settlement for matches that should be settled --------------------
        $settledCount = $this->settleMatches($matches);
        $this->command->info("✓ {$settledCount} matches settled, payouts applied");

        $this->command->info('');
        $this->command->info('Done. Log in with any of these accounts (password: "password"):');
        foreach (self::FAKE_USERS as $u) {
            $this->command->line("   {$u['email']}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Users & wallets
    // ─────────────────────────────────────────────────────────────────────────

    private function createUsersAndWallets(ForecastSeason $season): array
    {
        $created = [];

        foreach (self::FAKE_USERS as $data) {
            // firstOrCreate — safe to re-run the seeder without duplicates
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Wallet per user — one per season, so firstOrCreate guards re-runs
            $wallet = ForecastWallet::firstOrCreate(
                [
                    'user_id'   => $user->id,
                    'season_id' => $season->id,
                ],
                [
                    'currency' => $data['currency'],
                    'balance'  => ForecastWallet::STARTING_BALANCE,
                ]
            );

            $created[] = ['user' => $user, 'wallet' => $wallet];
        }

        return $created;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Matches — mix of states and types so every UI variant is visible
    // ─────────────────────────────────────────────────────────────────────────

    private function createMatches(ForecastSeason $season): array
    {
        // Pick some real players from the DB for the foreigner matches, so names,
        // flags and races render correctly. Fallback to generated names if the DB
        // has fewer than 8 eligible players.
        $pool = Player::whereNull('player_id')
            ->whereNotIn('race', ['Unknown'])
            ->whereNotNull('country_code')
            ->inRandomOrder()
            ->limit(16)
            ->get();

        $hasRealPlayers = $pool->count() >= 8;

        $matches = [];

        // ── Foreigner matches (settled wins/losses, open, locked) ────────────
        if ($hasRealPlayers) {
            // 3 open foreigner matches (locks in the future)
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: 24);
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: 48);
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: 72);

            // 2 locked but not settled (past lock time, no winner yet)
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: -1);
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: -2);

            // 3 settled foreigner matches
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: -48);
            $matches[] = $this->makeForeignerMatch($season, $pool->shift(), $pool->shift(), scheduledInHours: -72);
        } else {
            $this->command->warn('  (not enough real players — skipping foreigner matches)');
        }

        // ── Korean matches ───────────────────────────────────────────────────
        // Use fake Korean names since we don't have a guest table in context
        $matches[] = $this->makeKoreanMatch($season, 'Flash', 'Terran', 'Jaedong', 'Zerg', scheduledInHours: 12);
        $matches[] = $this->makeKoreanMatch($season, 'Bisu',  'Protoss', 'Stork',  'Protoss', scheduledInHours: -24);

        // ── Clan match ───────────────────────────────────────────────────────
        $matches[] = $this->makeClanMatch($season, 'Team Liquid', 'Evil Geniuses', scheduledInHours: 36);
        $matches[] = $this->makeClanMatch($season, 'SK Gaming',   'mYinsanity',    scheduledInHours: -36);

        // ── National match ───────────────────────────────────────────────────
        $matches[] = $this->makeNationalMatch($season, 'PL', 'DE', scheduledInHours: 96);
        $matches[] = $this->makeNationalMatch($season, 'SE', 'FI', scheduledInHours: -96);

        return $matches;
    }

    private function makeForeignerMatch(ForecastSeason $season, Player $a, Player $b, int $scheduledInHours): ForecastMatch
    {
        $eloA = $a->rating?->rating ?? 1000;
        $eloB = $b->rating?->rating ?? 1000;
        $odds = ForecastMatch::calculateOdds($eloA, $eloB);

        $scheduledAt = now()->addHours($scheduledInHours);
        $lockedAt    = $scheduledAt->copy()->subHour();

        return ForecastMatch::create([
            'season_id'     => $season->id,
            'match_type'    => 'foreigner',
            'player_a_id'   => $a->id,
            'player_b_id'   => $b->id,
            'player_a_race' => $a->race,
            'player_b_race' => $b->race,
            'odds_a'        => $odds['odds_a'],
            'odds_b'        => $odds['odds_b'],
            'multiplier'    => 1.00,
            'scheduled_at'  => $scheduledAt,
            'locked_at'     => $lockedAt,
        ]);
    }

    private function makeKoreanMatch(ForecastSeason $season, string $nameA, string $raceA, string $nameB, string $raceB, int $scheduledInHours): ForecastMatch
    {
        $scheduledAt = now()->addHours($scheduledInHours);
        $lockedAt    = $scheduledAt->copy()->subHour();
        $multiplier  = 1.50;

        return ForecastMatch::create([
            'season_id'        => $season->id,
            'match_type'       => 'korean',
            'player_a_name'    => $nameA,
            'player_b_name'    => $nameB,
            'player_a_race'    => $raceA,
            'player_b_race'    => $raceB,
            'player_a_country' => 'KR',
            'player_b_country' => 'KR',
            'odds_a'           => $multiplier,
            'odds_b'           => $multiplier,
            'multiplier'       => $multiplier,
            'scheduled_at'     => $scheduledAt,
            'locked_at'        => $lockedAt,
        ]);
    }

    private function makeClanMatch(ForecastSeason $season, string $nameA, string $nameB, int $scheduledInHours): ForecastMatch
    {
        $scheduledAt = now()->addHours($scheduledInHours);
        $lockedAt    = $scheduledAt->copy()->subHour();
        $multiplier  = 1.80;

        return ForecastMatch::create([
            'season_id'     => $season->id,
            'match_type'    => 'clan',
            'player_a_name' => $nameA,
            'player_b_name' => $nameB,
            'player_a_race' => 'Unknown',
            'player_b_race' => 'Unknown',
            'odds_a'        => $multiplier,
            'odds_b'        => $multiplier,
            'multiplier'    => $multiplier,
            'scheduled_at'  => $scheduledAt,
            'locked_at'     => $lockedAt,
        ]);
    }

    private function makeNationalMatch(ForecastSeason $season, string $codeA, string $codeB, int $scheduledInHours): ForecastMatch
    {
        $scheduledAt = now()->addHours($scheduledInHours);
        $lockedAt    = $scheduledAt->copy()->subHour();
        $multiplier  = 2.00;

        $countries = collect(config('countries'));
        $nameA = $countries->firstWhere('code', $codeA)['name'] ?? $codeA;
        $nameB = $countries->firstWhere('code', $codeB)['name'] ?? $codeB;

        return ForecastMatch::create([
            'season_id'        => $season->id,
            'match_type'       => 'national',
            'player_a_name'    => $nameA,
            'player_b_name'    => $nameB,
            'player_a_race'    => 'Unknown',
            'player_b_race'    => 'Unknown',
            'player_a_country' => $codeA,
            'player_b_country' => $codeB,
            'odds_a'           => $multiplier,
            'odds_b'           => $multiplier,
            'multiplier'       => $multiplier,
            'scheduled_at'     => $scheduledAt,
            'locked_at'        => $lockedAt,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Predictions — skewed so the crowd % bar and leaderboard have variety.
    // We bypass ForecastService::placeBet to avoid the "match must be open"
    // validation, because we also want predictions on already-locked matches.
    // ─────────────────────────────────────────────────────────────────────────

    private function createPredictions(array $matches, array $users): int
    {
        $count = 0;

        foreach ($matches as $match) {
            // Decide how many users pick this match — 4 to 7 out of 8
            $pickerCount = rand(4, 7);
            $pickers = collect($users)->shuffle()->take($pickerCount);

            // Bias the crowd split so some matches show a clear favorite,
            // others are more split. 0..100 — probability of picking side A.
            $biasForA = rand(25, 75);

            foreach ($pickers as $entry) {
                $user   = $entry['user'];
                $wallet = $entry['wallet']->fresh(); // re-read balance after previous bets

                if ($wallet->balance < 2) {
                    continue; // skip broke users
                }

                $pickA = rand(1, 100) <= $biasForA;
                $stake = min((float) rand(3, 15), (float) $wallet->balance);

                $this->placePrediction($match, $user, $wallet, $pickA, $stake);
                $count++;
            }
        }

        return $count;
    }

    private function placePrediction(
        ForecastMatch $match,
        User $user,
        ForecastWallet $wallet,
        bool $pickA,
        float $stake,
    ): void {
        DB::transaction(function () use ($match, $user, $wallet, $pickA, $stake) {
            // Replicate the odds math from ForecastService::placeBet / placeBetBySide
            $pickedOdds = ($pickA ? (float) $match->odds_a : (float) $match->odds_b) * (float) $match->multiplier;
            $otherOdds  = ($pickA ? (float) $match->odds_b : (float) $match->odds_a) * (float) $match->multiplier;
            $pickedRace = $pickA ? $match->player_a_race : $match->player_b_race;

            $bonus = ForecastPrediction::resolveBonusMultiplier(
                currency:   $wallet->currency,
                pickedRace: $pickedRace,
                pickedOdds: $pickedOdds,
                otherOdds:  $otherOdds,
            );

            $potentialPayout = round($stake * $pickedOdds * $bonus, 2);

            // Deduct stake
            $wallet->decrement('balance', $stake);

            // For foreigner matches we store pick_player_id, for others pick_side
            $pickPlayerId = null;
            $pickSide     = null;
            if ($match->match_type === 'foreigner') {
                $pickPlayerId = $pickA ? $match->player_a_id : $match->player_b_id;
            } else {
                $pickSide = $pickA ? 'a' : 'b';
            }

            ForecastPrediction::create([
                'user_id'          => $user->id,
                'match_id'         => $match->id,
                'wallet_id'        => $wallet->id,
                'pick_player_id'   => $pickPlayerId,
                'pick_side'        => $pickSide,
                'stake'            => $stake,
                'odds_at_time'     => $pickedOdds,
                'bonus_multiplier' => $bonus,
                'potential_payout' => $potentialPayout,
                'result'           => 'pending',
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settlement — mark some matches as finished and pay out winners.
    // Criterion: matches whose scheduled_at is > 24h in the past get settled.
    // ─────────────────────────────────────────────────────────────────────────

    private function settleMatches(array $matches): int
    {
        $settled = 0;

        foreach ($matches as $match) {
            if ($match->scheduled_at->diffInHours(now(), false) < 24) {
                continue; // too recent — leave unsettled
            }

            // Coin flip winner, slightly biased so it's never deterministic
            $winnerIsA = rand(0, 1) === 1;

            DB::transaction(function () use ($match, $winnerIsA) {
                if ($match->match_type === 'foreigner') {
                    $match->update([
                        'winner_id'  => $winnerIsA ? $match->player_a_id : $match->player_b_id,
                        'settled_at' => now(),
                    ]);
                    $this->payoutMatch($match, fn($p) => $p->pick_player_id === $match->winner_id);
                } else {
                    $match->update([
                        'winner_side' => $winnerIsA ? 'a' : 'b',
                        'settled_at'  => now(),
                    ]);
                    $this->payoutMatch($match, fn($p) => $p->pick_side === $match->winner_side);
                }
            });

            $settled++;
        }

        return $settled;
    }

    /**
     * Pay out a settled match — mirrors ForecastService::payoutPredictions.
     */
    private function payoutMatch(ForecastMatch $match, callable $isWinner): void
    {
        $match->predictions()
            ->where('result', 'pending')
            ->with('wallet')
            ->each(function ($prediction) use ($isWinner) {
                $won = $isWinner($prediction);

                if ($won) {
                    $prediction->wallet->increment('balance', $prediction->potential_payout);
                }

                $prediction->update([
                    'result'        => $won ? 'won' : 'lost',
                    'actual_payout' => $won ? $prediction->potential_payout : null,
                ]);
            });
    }
}