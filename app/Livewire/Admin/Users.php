<?php

namespace App\Livewire\Admin;

use App\Models\ForecastSeason;
use App\Models\ForecastWallet;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    // ID of the user whose forecast detail panel is open (null = none)
    public ?int $expandedUserId = null;

    // Inline balance editor state
    public ?int $editBalanceUserId = null;
    public string $editBalanceValue = '';
    public ?string $editBalanceError = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updateRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);

        // Prevent admin from demoting themselves
        if ($user->id === auth()->id()) {
            return;
        }

        $user->update(['role' => $role]);

        $this->dispatch('role-updated');
    }

    /**
     * Toggle the expanded forecast detail panel for a given user.
     */
    public function toggleExpand(int $userId): void
    {
        $this->expandedUserId = $this->expandedUserId === $userId ? null : $userId;
    }

    // ── Computed ──────────────────────────────────────

    #[Computed]
    public function currentSeason(): ?ForecastSeason
    {
        return ForecastSeason::current();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
            )
            ->orderBy('id')
            ->withCount(['comments', 'reactions'])
            ->paginate(25);
    }

    /**
     * Load full forecast data for a single user in the current season.
     * Called on-demand when a row is expanded — avoids loading for all users.
     *
     * Returns null if there is no active season or the user has no wallet.
     */
    #[Computed]
    public function expandedWallet(): ?ForecastWallet
    {
        if (! $this->expandedUserId || ! $this->currentSeason) {
            return null;
        }

        $wallet = ForecastWallet::where('user_id', $this->expandedUserId)
            ->where('season_id', $this->currentSeason->id)
            ->with([
                'predictions' => fn($q) => $q
                    ->with(['match.playerA', 'match.playerB', 'pickedPlayer'])
                    ->orderByDesc('created_at')
                    ->limit(10), // show last 10 bets in the panel
            ])
            ->first();

        if (! $wallet) {
            return null;
        }

        // Attach aggregated stats as dynamic attributes so the view stays logic-free
        $settled = $wallet->predictions->whereIn('result', ['won', 'lost']);

        $wallet->stats_total      = $wallet->predictions->count();
        $wallet->stats_settled    = $settled->count();
        $wallet->stats_pending    = $wallet->predictions->where('result', 'pending')->count();
        $wallet->stats_won        = $settled->where('result', 'won')->count();
        $wallet->stats_lost       = $settled->where('result', 'lost')->count();
        $wallet->stats_profit     = round(
            (float) $settled->sum('actual_payout') - (float) $settled->sum('stake'),
            2,
        );
        $wallet->stats_accuracy   = $settled->count() > 0
            ? round($wallet->stats_won / $settled->count() * 100, 1)
            : null;

        return $wallet;
    }

    /**
     * Open the inline balance editor for a specific user's wallet.
     */
    public function startEditBalance(int $userId): void
    {
        if (! $this->currentSeason) {
            return;
        }

        $wallet = ForecastWallet::where('user_id', $userId)
            ->where('season_id', $this->currentSeason->id)
            ->first();

        if (! $wallet) {
            return;
        }

        $this->editBalanceUserId = $userId;
        $this->editBalanceValue  = (string) intval($wallet->balance);
        $this->editBalanceError  = null;
    }

    public function cancelEditBalance(): void
    {
        $this->editBalanceUserId = null;
        $this->editBalanceValue  = '';
        $this->editBalanceError  = null;
    }

    /**
     * Save the manually adjusted balance.
     * Allows values from 0 to 9999 — no upper limit enforced by game rules,
     * but we cap it to prevent typos from giving someone absurd amounts.
     */
    public function updateBalance(): void
    {
        $this->editBalanceError = null;

        $value = trim($this->editBalanceValue);

        if (! is_numeric($value) || (float) $value < 0 || (float) $value > 9999) {
            $this->editBalanceError = 'Enter a number between 0 and 9999.';
            return;
        }

        if (! $this->currentSeason || ! $this->editBalanceUserId) {
            return;
        }

        $wallet = ForecastWallet::where('user_id', $this->editBalanceUserId)
            ->where('season_id', $this->currentSeason->id)
            ->first();

        if (! $wallet) {
            $this->editBalanceError = 'Wallet not found.';
            return;
        }

        $wallet->balance = round((float) $value, 2);
        $wallet->save();

        // Reset expanded wallet computed cache so the panel refreshes
        unset($this->expandedWallet);

        $this->editBalanceUserId = null;
        $this->editBalanceValue  = '';

        $this->dispatch('balance-updated');
    }

    public function render()
    {
        return view('livewire.admin.users');
    }
}