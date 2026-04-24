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
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Match list + all match-management modals.
 *
 * Handles:
 *   - Fetching open/settled matches for the active season
 *   - Pre-computing crowd % per match (no N+1)
 *   - Bet modal (place prediction)
 *   - Settle modal (admin declares winner)
 *   - Add/Edit match modal (admin creates matches)
 *   - Delete confirmation
 *
 * Receives $view ('open' or 'settled') from the parent as a property.
 */
class MatchList extends Component
{
    public string $view = 'open'; // open | settled

    // ── Bet modal ─────────────────────────────────────
    public bool $showBetModal = false;
    public ?int $bettingMatchId = null;
    public ?int $pickedPlayerId = null;
    public ?string $pickedSide = null;
    public string $stake = '';

    // ── Add/edit match modal ──────────────────────────
    public bool $showMatchModal = false;
    public ?int $editingMatchId = null;
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

    // korean — free-text / guest search
    public string $koreanASearch = '';
    public string $koreanBSearch = '';
    public string $koreanAName = '';
    public string $koreanBName = '';
    public string $koreanARace = 'Unknown';
    public string $koreanBRace = 'Unknown';

    // clan
    public string $clanAName = '';
    public string $clanBName = '';

    // national
    public string $nationalACode = '';
    public string $nationalBCode = '';

    // ── Settle modal ──────────────────────────────────
    public ?int $settlingMatchId = null;
    public ?string $winnerSide = null;

    // ── Delete confirmation ───────────────────────────
    public ?int $confirmingDeleteId = null;

    public function mount(): void
    {
        $this->scheduledAt = now()->format('Y-m-d\TH:i');
        $this->lockedAt    = now()->subHour()->format('Y-m-d\TH:i');
    }

    // ── Computed ──────────────────────────────────────

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
            $query->whereNull('winner_id')->whereNull('winner_side');
        } else {
            $query->where(fn($q) => $q->whereNotNull('winner_id')->orWhereNotNull('winner_side'));
        }

        return $query->get();
    }

    // Player search for admin add/edit modal

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
    public function countries(): array
    {
        return collect(config('countries', []))
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    // ── External events ───────────────────────────────

    #[On('wallet-updated')]
    public function refreshWallet(): void
    {
        unset($this->wallet);
    }

    // NOTE: Only showing the patch for openBetModal — the rest of the class is unchanged.
    // Apply ONLY the body of openBetModal below to your existing MatchList.php.
    // Everything else (placeBet, settleMatch, add/edit/delete, computed props) stays as-is.
     
    /**
     * openBetModal — now accepts an optional pre-selected side.
     *
     * Called from the match card's per-side pick buttons:
     *   <button wire:click="openBetModal({id}, 'a')">Pick NameA</button>
     *
     * When $side is provided, we pre-fill pickedSide (non-foreigner) or pickedPlayerId
     * (foreigner) so the bet modal opens directly on the stake step — the user already
     * declared WHO, only needs to declare HOW MUCH.
     *
     * When $side is null (legacy call path or programmatic open), the modal falls back
     * to the original "pick side first" flow.
     */
    public function openBetModal(int $matchId, ?string $side = null): void
    {
        abort_unless(auth()->check(), 403);
     
        if (! $this->wallet) {
            // Bubble up to parent to show the currency modal
            $this->dispatch('request-currency-modal');
            return;
        }
     
        $this->bettingMatchId = $matchId;
        $this->pickedPlayerId = null;
        $this->pickedSide     = null;
        $this->stake          = '';
        $this->showBetModal   = true;
     
        // Pre-select a side if the card told us which button the user clicked.
        if ($side === 'a' || $side === 'b') {
            $match = ForecastMatch::findOrFail($matchId);
     
            if ($match->match_type === 'foreigner') {
                // Foreigner matches use pick_player_id (not pick_side)
                $this->pickedPlayerId = $side === 'a'
                    ? $match->player_a_id
                    : $match->player_b_id;
            } else {
                $this->pickedSide = $side;
            }
        }
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
        $this->dispatch('prediction-placed');
    }

    // ── Add / edit match (admin) ──────────────────────

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
            $this->koreanARace = $match->player_a_race ?? 'Unknown';
            $this->koreanBName = $match->player_b_name ?? '';
            $this->koreanBRace = $match->player_b_race ?? 'Unknown';
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
            'lockedAt'    => 'required|date|before_or_equal:scheduledAt',
            'multiplier'  => 'required|numeric|min:0.1',
        ]);

        $data = [
            'season_id'    => $this->season->id,
            'match_type'   => $this->matchType,
            'scheduled_at' => $this->scheduledAt,
            'locked_at'    => $this->lockedAt,
            'multiplier'   => $this->multiplier,
            'event_id'     => $this->eventId,
        ];

        if ($this->matchType === 'foreigner') {
            $this->validate([
                'playerAId' => 'required|integer|different:playerBId',
                'playerBId' => 'required|integer',
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

    // ── Settle match (admin) ──────────────────────────

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

        if ($match->match_type === 'foreigner') {
            $winnerId = $this->winnerSide === 'a' ? $match->player_a_id : $match->player_b_id;
            $winner   = Player::findOrFail($winnerId);
            app(ForecastService::class)->settleMatch($match, $winner, auth()->user());
        } else {
            app(ForecastService::class)->settleMatchBySide($match, $this->winnerSide, auth()->user());
        }

        $this->settlingMatchId = null;
        $this->winnerSide      = null;
        unset($this->matches);

        $this->dispatch('match-settled');
    }

    // ── Delete match (admin) ──────────────────────────

    public function deleteMatch(): void
    {
        abort_if(! auth()->user()?->canManageGames(), 403);

        $match = ForecastMatch::findOrFail($this->confirmingDeleteId);
        $match->delete();

        $this->confirmingDeleteId = null;
        unset($this->matches);

        $this->dispatch('match-deleted');
    }

    // ── Player / guest search (admin add-match) ───────

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

    // ── Private helpers ───────────────────────────────

    private function resetMatchForm(): void
    {
        $this->editingMatchId = null;
        $this->matchType      = 'foreigner';
        $this->scheduledAt    = now()->format('Y-m-d\TH:i');
        $this->lockedAt       = now()->subHour()->format('Y-m-d\TH:i');
        $this->multiplier     = 1.50;
        $this->eventId        = null;

        $this->playerAId = null; $this->playerAName = ''; $this->playerASearch = '';
        $this->playerBId = null; $this->playerBName = ''; $this->playerBSearch = '';
        $this->koreanAName = ''; $this->koreanARace = 'Unknown'; $this->koreanASearch = '';
        $this->koreanBName = ''; $this->koreanBRace = 'Unknown'; $this->koreanBSearch = '';
        $this->clanAName = ''; $this->clanBName = '';
        $this->nationalACode = ''; $this->nationalBCode = '';
    }

    private function searchPlayers(string $search, array $excludeIds = []): Collection
    {
        if (strlen($search) < 2) {
            return collect();
        }

        $playerIds = PlayerName::where('name', 'like', '%' . $search . '%')
            ->limit(10)
            ->pluck('player_id')
            ->unique();

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id') // main players only (not aliases)
            ->when(array_filter($excludeIds), fn($q) => $q->whereNotIn('id', array_filter($excludeIds)))
            ->limit(10)
            ->get();
    }

    public function updatedView()
    {
        unset($this->matches);
    }

    private function searchKorean(string $search, ?string $exclude = null): array
    {
        if (strlen($search) < 2) {
            return [];
        }

        // Try to read a guest list from config if the project exposes one;
        // otherwise just return a single "freeform" option with the typed name.
        $guests = config('forecast_guests', []);

        return collect($guests)
            ->filter(fn($g) => stripos($g['name'] ?? '', $search) !== false)
            ->when($exclude, fn($c) => $c->reject(fn($g) => $g['name'] === $exclude))
            ->take(10)
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.forecast.match-list');
    }
}