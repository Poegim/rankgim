<?php

namespace App\Livewire\Forecast;

use App\Models\ForecastMatch;
use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use App\Models\Player;
use App\Models\PlayerName;
use App\Services\ForecastService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $view = 'open'; // open | settled

    // ── Currency selection ────────────────────────────
    public bool $showCurrencyModal = false;
    public string $selectedCurrency = 'minerals';

    // ── Bet modal ─────────────────────────────────────
    public bool $showBetModal = false;
    public ?int $bettingMatchId = null;
    public ?int $pickedPlayerId = null;  // foreigner only
    public ?string $pickedSide = null;   // korean/clan/national: 'a' or 'b'
    public string $stake = '';

    // ── Add/edit match modal ──────────────────────────
    public bool $showMatchModal = false;
    public ?int $editingMatchId = null;

    // Common match fields
    public string $matchType = 'foreigner';
    public string $scheduledAt = '';
    public string $lockedAt = '';
    public ?int $eventId = null;
    public float $multiplier = 1.50;

    // foreigner — search in players table
    public string $playerASearch = '';
    public string $playerBSearch = '';
    public ?int $playerAId = null;
    public ?int $playerBId = null;
    public string $playerAName = '';
    public string $playerBName = '';

    // korean — search in events_guests config
    public string $koreanASearch = '';
    public string $koreanBSearch = '';
    public string $koreanAName = '';
    public string $koreanBName = '';
    public string $koreanARace = 'Unknown';
    public string $koreanBRace = 'Unknown';

    // clan — free text inputs
    public string $clanAName = '';
    public string $clanBName = '';

    // national — country selects
    public string $nationalACode = '';
    public string $nationalBCode = '';

    // ── Settle match ──────────────────────────────────
    public ?int $settlingMatchId = null;
    public ?string $winnerSide = null; // 'a' or 'b'

    // ── Delete confirmation ───────────────────────────
    public ?int $confirmingDeleteId = null;

    // ── Season management ─────────────────────────────
    public bool $showSeasonModal = false;
    public string $seasonName = '';
    public string $seasonStartsAt = '';

    public function mount(): void
    {
        $this->scheduledAt = now()->format('Y-m-d\TH:i');
        $this->lockedAt    = now()->subHour()->format('Y-m-d\TH:i');
    }

    // ── Computed ───────────────────────────────────────

    #[Computed]
    public function season(): ?ForecastSeason
    {
        return ForecastSeason::current();
    }

    #[Computed]
    public function wallet(): ?ForecastWallet
    {
        if (! auth()->check() || ! $this->season) {
            return null;
        }

        return ForecastWallet::where('user_id', auth()->id())
            ->where('season_id', $this->season->id)
            ->first();
    }

    #[Computed]
    public function matches(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        $query = ForecastMatch::where('season_id', $this->season->id)
            ->with(['playerA', 'playerB', 'winner', 'event', 'predictions'])
            ->orderBy('scheduled_at', 'asc');

        if ($this->view === 'open') {
            // Not settled = no winner_id AND no winner_side
            $query->whereNull('winner_id')->whereNull('winner_side');
        } else {
            // Settled = has either winner_id (foreigner) or winner_side (others)
            $query->where(fn($q) => $q->whereNotNull('winner_id')->orWhereNotNull('winner_side'));
        }

        return $query->get();
    }

    #[Computed]
    public function ranking(): Collection
    {
        if (! $this->season) {
            return collect();
        }

        return ForecastWallet::where('season_id', $this->season->id)
            ->whereHas('predictions', fn($q) => $q->whereIn('result', ['won', 'lost']))
            ->with('user')
            ->get()
            ->map(function ($wallet) {
                $settled = $wallet->predictions()
                    ->whereIn('result', ['won', 'lost'])
                    ->get();

                $wallet->profit = round(
                    $settled->sum('actual_payout') - $settled->sum('stake'),
                    2
                );

                return $wallet;
            })
            ->sortByDesc('profit')
            ->values();
    }

    #[Computed]
    public function playerAResults(): Collection
    {
        return $this->searchPlayers($this->playerASearch, [$this->playerBId]);
    }

    #[Computed]
    public function playerBResults(): Collection
    {
        return $this->searchPlayers($this->playerBSearch, [$this->playerAId]);
    }

    #[Computed]
    public function koreanAResults(): array
    {
        return $this->searchKorean($this->koreanASearch, $this->koreanBName);
    }

    #[Computed]
    public function koreanBResults(): array
    {
        return $this->searchKorean($this->koreanBSearch, $this->koreanAName);
    }

    #[Computed]
    public function currencies(): array
    {
        return ForecastWallet::CURRENCIES;
    }

    #[Computed]
    public function countries(): array
    {
        return collect(config('countries', []))
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    // ── Currency selection ────────────────────────────

    public function openCurrencyModal(): void
    {
        $this->showCurrencyModal = true;
    }

    public function createWallet(): void
    {
        $this->validate(['selectedCurrency' => 'required|in:minerals,khaydarin,biomass,credits']);

        app(ForecastService::class)->getOrCreateWallet(auth()->user(), $this->selectedCurrency);

        $this->showCurrencyModal = false;
        unset($this->wallet);
    }

    // ── Wallet reset ──────────────────────────────────

    public function resetWallet(): void
    {
        abort_if(! $this->wallet, 403);
        app(ForecastService::class)->resetWallet($this->wallet);
        unset($this->wallet);
    }

    // ── Bet modal ─────────────────────────────────────

    public function openBetModal(int $matchId): void
    {
        if (! $this->wallet) {
            $this->showCurrencyModal = true;
            return;
        }

        $this->bettingMatchId = $matchId;
        $this->pickedPlayerId = null;
        $this->pickedSide     = null;
        $this->stake          = '';
        $this->showBetModal   = true;
    }

    public function placeBet(): void
    {
        $this->validate(['stake' => 'required|numeric|min:1']);

        $match = ForecastMatch::findOrFail($this->bettingMatchId);

        if ($match->match_type === 'foreigner') {
            $this->validate(['pickedPlayerId' => 'required|integer']);
            $player = Player::findOrFail($this->pickedPlayerId);

            app(ForecastService::class)->placeBet(
                user:         auth()->user(),
                match:        $match,
                pickedPlayer: $player,
                stake:        (float) $this->stake,
            );
        } else {
            $this->validate(['pickedSide' => 'required|in:a,b']);

            app(ForecastService::class)->placeBetBySide(
                user:  auth()->user(),
                match: $match,
                side:  $this->pickedSide,
                stake: (float) $this->stake,
            );
        }

        $this->showBetModal   = false;
        $this->bettingMatchId = null;
        $this->stake          = '';
        $this->pickedPlayerId = null;
        $this->pickedSide     = null;

        unset($this->wallet, $this->matches);
    }

    // ── Add / edit match ──────────────────────────────

    public function openAddMatchModal(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        $this->resetMatchForm();
        $this->showMatchModal = true;
    }

    public function openEditMatchModal(int $matchId): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        $match = ForecastMatch::findOrFail($matchId);

        $this->editingMatchId = $match->id;
        $this->matchType      = $match->match_type;
        $this->scheduledAt    = $match->scheduled_at->format('Y-m-d\TH:i');
        $this->lockedAt       = $match->locked_at->format('Y-m-d\TH:i');
        $this->multiplier     = (float) $match->multiplier;
        $this->eventId        = $match->event_id;

        if ($match->match_type === 'foreigner') {
            $this->playerAId   = $match->player_a_id;
            $this->playerAName = $match->playerA?->name ?? '';
            $this->playerBId   = $match->player_b_id;
            $this->playerBName = $match->playerB?->name ?? '';
        } elseif ($match->match_type === 'korean') {
            $this->koreanAName = $match->player_a_name ?? '';
            $this->koreanARace = $match->player_a_race;
            $this->koreanBName = $match->player_b_name ?? '';
            $this->koreanBRace = $match->player_b_race;
        } elseif ($match->match_type === 'clan') {
            $this->clanAName = $match->player_a_name ?? '';
            $this->clanBName = $match->player_b_name ?? '';
        } elseif ($match->match_type === 'national') {
            $this->nationalACode = $match->player_a_country ?? '';
            $this->nationalBCode = $match->player_b_country ?? '';
        }

        $this->showMatchModal = true;
    }

    public function saveMatch(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);

        $this->validate([
            'matchType'   => 'required|in:foreigner,korean,clan,national',
            'scheduledAt' => 'required|date',
            'lockedAt'    => 'required|date|before:scheduledAt',
            'multiplier'  => 'required|numeric|min:0.1',
        ]);

        $data = [
            'season_id'        => $this->season->id,
            'event_id'         => $this->eventId ?: null,
            'match_type'       => $this->matchType,
            'multiplier'       => $this->multiplier,
            'scheduled_at'     => $this->scheduledAt,
            'locked_at'        => $this->lockedAt,
            // Reset all player fields — filled selectively below
            'player_a_id'      => null,
            'player_b_id'      => null,
            'player_a_name'    => null,
            'player_b_name'    => null,
            'player_a_race'    => 'Unknown',
            'player_b_race'    => 'Unknown',
            'player_a_country' => null,
            'player_b_country' => null,
        ];

        if ($this->matchType === 'foreigner') {
        $this->validate([
            'playerAId' => 'required|integer|exists:players,id',
            'playerBId' => 'required|integer|exists:players,id|different:playerAId',
        ]);

        $playerA = Player::findOrFail($this->playerAId);
        $playerB = Player::findOrFail($this->playerBId);

        $eloA = $playerA->rating?->rating ?? 1000;
        $eloB = $playerB->rating?->rating ?? 1000;
        $odds = ForecastMatch::calculateOdds($eloA, $eloB);

        $data = array_merge($data, [
            'player_a_id'   => $this->playerAId,
            'player_b_id'   => $this->playerBId,
            'player_a_race' => $playerA->race,
            'player_b_race' => $playerB->race,
            'odds_a'        => $odds['odds_a'],
            'odds_b'        => $odds['odds_b'],
        ]);
    } elseif ($this->matchType === 'korean') {
            $this->validate([
                'koreanAName' => 'required|string|max:100',
                'koreanBName' => 'required|string|max:100|different:koreanAName',
            ]);

            $data = array_merge($data, [
                'player_a_name' => $this->koreanAName,
                'player_b_name' => $this->koreanBName,
                'player_a_race' => $this->koreanARace,
                'player_b_race' => $this->koreanBRace,
                'odds_a'        => $this->multiplier,
                'odds_b'        => $this->multiplier,
            ]);
            
        } elseif ($this->matchType === 'clan') {
            $this->validate([
                'clanAName' => 'required|string|max:100',
                'clanBName' => 'required|string|max:100|different:clanAName',
            ]);

            $data = array_merge($data, [
                'player_a_name' => $this->clanAName,
                'player_b_name' => $this->clanBName,
                'odds_a'        => $this->multiplier,
                'odds_b'        => $this->multiplier,
            ]);
        } elseif ($this->matchType === 'national') {
            $this->validate([
                'nationalACode' => 'required|string|size:2|different:nationalBCode',
                'nationalBCode' => 'required|string|size:2',
            ]);

            $countryA = collect(config('countries'))->firstWhere('code', $this->nationalACode);
            $countryB = collect(config('countries'))->firstWhere('code', $this->nationalBCode);

            $data = array_merge($data, [
                'player_a_name'    => $countryA['name'] ?? $this->nationalACode,
                'player_b_name'    => $countryB['name'] ?? $this->nationalBCode,
                'player_a_country' => $this->nationalACode,
                'player_b_country' => $this->nationalBCode,
                'odds_a'           => $this->multiplier,
                'odds_b'           => $this->multiplier,
            ]);
        }

        if ($this->editingMatchId) {
            ForecastMatch::findOrFail($this->editingMatchId)->update($data);
        } else {
            ForecastMatch::create($data);
        }

        $this->showMatchModal = false;
        $this->resetMatchForm();
        unset($this->matches);
    }

    // ── Settle match ──────────────────────────────────

    public function openSettleModal(int $matchId): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        $this->settlingMatchId = $matchId;
        $this->winnerSide      = null;
    }

    public function settleMatch(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        $this->validate(['winnerSide' => 'required|in:a,b']);

        $match = ForecastMatch::findOrFail($this->settlingMatchId);

        app(ForecastService::class)->settleMatchBySide($match, $this->winnerSide, auth()->user());

        $this->settlingMatchId = null;
        $this->winnerSide      = null;

        unset($this->matches, $this->ranking);
    }

    // ── Delete match ──────────────────────────────────

    public function deleteMatch(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);
        if (! $this->confirmingDeleteId) {
            return;
        }

        ForecastMatch::findOrFail($this->confirmingDeleteId)->delete();

        $this->confirmingDeleteId = null;
        unset($this->matches, $this->wallet);
    }

    // ── Season ────────────────────────────────────────

    public function openSeasonModal(): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);
        $this->seasonName      = 'Season ' . now()->year;
        $this->seasonStartsAt  = now()->format('Y-m-d\TH:i');
        $this->showSeasonModal = true;
    }

    public function createSeason(): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);
        $this->validate([
            'seasonName'     => 'required|string|max:255',
            'seasonStartsAt' => 'required|date',
        ]);

        ForecastSeason::where('is_active', true)->update(['is_active' => false]);

        ForecastSeason::create([
            'name'       => $this->seasonName,
            'starts_at'  => $this->seasonStartsAt,
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        $this->showSeasonModal = false;
        unset($this->season, $this->matches, $this->wallet, $this->ranking);
    }

    public function closeSeason(): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);
        app(ForecastService::class)->closeSeason($this->season);
        unset($this->season, $this->matches, $this->ranking);
    }

    // ── Korean guest selection ────────────────────────

    public function selectKoreanA(string $name, string $race): void
    {
        $this->koreanAName   = $name;
        $this->koreanARace   = $race;
        $this->koreanASearch = '';
        unset($this->koreanAResults);
    }

    public function selectKoreanB(string $name, string $race): void
    {
        $this->koreanBName   = $name;
        $this->koreanBRace   = $race;
        $this->koreanBSearch = '';
        unset($this->koreanBResults);
    }

    // ── Foreigner player selection ────────────────────

    public function selectPlayerA(int $playerId, string $playerName): void
    {
        $this->playerAId     = $playerId;
        $this->playerAName   = $playerName;
        $this->playerASearch = '';
        unset($this->playerAResults);
    }

    public function selectPlayerB(int $playerId, string $playerName): void
    {
        $this->playerBId     = $playerId;
        $this->playerBName   = $playerName;
        $this->playerBSearch = '';
        unset($this->playerBResults);
    }

    public function setView(string $view): void
    {
        $this->view = $view;
        unset($this->matches);
    }

    // ── Private helpers ───────────────────────────────

    private function searchPlayers(string $search, array $excludeIds = []): Collection
    {
        if (strlen($search) < 2) {
            return collect();
        }

        $playerIds = PlayerName::where('name', 'like', '%' . $search . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id')
            ->whereNotIn('id', array_filter($excludeIds))
            ->limit(8)
            ->get();
    }

    private function searchKorean(string $search, string $excludeName = ''): array
    {
        if (strlen($search) < 2) {
            return [];
        }

        $query = strtolower($search);

        return collect(config('events_guests', []))
            ->filter(fn($g) =>
                str_contains(strtolower($g['name']), $query)
                && strtolower($g['name']) !== strtolower($excludeName)
            )
            ->values()
            ->take(8)
            ->toArray();
    }

    private function resetMatchForm(): void
    {
        $this->editingMatchId = null;
        $this->matchType      = 'foreigner';
        $this->scheduledAt    = now()->format('Y-m-d\TH:i');
        $this->lockedAt       = now()->subHour()->format('Y-m-d\TH:i');
        $this->multiplier     = 1.50;
        $this->eventId        = null;
        // foreigner
        $this->playerAId      = null;
        $this->playerBId      = null;
        $this->playerAName    = '';
        $this->playerBName    = '';
        $this->playerASearch  = '';
        $this->playerBSearch  = '';
        // korean
        $this->koreanAName    = '';
        $this->koreanBName    = '';
        $this->koreanARace    = 'Unknown';
        $this->koreanBRace    = 'Unknown';
        $this->koreanASearch  = '';
        $this->koreanBSearch  = '';
        // clan
        $this->clanAName      = '';
        $this->clanBName      = '';
        // national
        $this->nationalACode  = '';
        $this->nationalBCode  = '';
        unset($this->playerAResults, $this->playerBResults, $this->koreanAResults, $this->koreanBResults);
    }

    public function render()
    {
        return view('livewire.forecast.index');
    }
}