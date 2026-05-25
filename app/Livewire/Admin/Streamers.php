<?php

namespace App\Livewire\Admin;

use App\Models\Streamer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Unified admin CRUD for the streamers whitelist (both SOOP and Twitch).
 *
 * Replaces the old SoopStreamers component. Adds a platform tab strip at the
 * top so admin can switch between viewing/editing SOOP and Twitch entries.
 * Uniqueness is per-platform (matches the streamers table unique constraint),
 * so the same user_id can theoretically exist on both platforms without collision.
 */
class Streamers extends Component
{
    /**
     * Currently-selected platform tab. Persisted to URL so the admin can
     * bookmark a specific platform view.
     */
    #[Url(as: 'platform')]
    public string $platform = Streamer::PLATFORM_SOOP;

    public array $races = Streamer::RACES;

    public array $platformTabs = [
        Streamer::PLATFORM_SOOP   => 'SOOP',
        Streamer::PLATFORM_TWITCH => 'Twitch',
    ];

    // ── Form: add new ─────────────────────────────────

    #[Validate('required|string|max:100')]
    public string $newUserId = '';

    #[Validate('required|string|max:100')]
    public string $newLabel = '';

    #[Validate('nullable|in:,zerg,protoss,terran,random')]
    public ?string $newRace = null;

    // ── Form: inline edit ─────────────────────────────

    public ?int $editingId = null;

    #[Validate('required|string|max:100')]
    public string $editLabel = '';

    #[Validate('nullable|in:,zerg,protoss,terran,random')]
    public ?string $editRace = null;

    // ── Computed ──────────────────────────────────────

    #[Computed]
    public function streamers()
    {
        return Streamer::query()
            ->where('platform', $this->platform)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Per-platform placeholder text + helper hint for the user_id input.
     * Keeps the UI honest about platform-specific identifier conventions:
     *   SOOP    — numeric/alphanumeric account id (e.g. "afstar1")
     *   Twitch  — channel login slug (e.g. "zzzeropl"), lowercased automatically
     */
    #[Computed]
    public function userIdPlaceholder(): string
    {
        return $this->platform === Streamer::PLATFORM_TWITCH ? 'zzzeropl' : 'afstar1';
    }

    #[Computed]
    public function userIdHelp(): string
    {
        return $this->platform === Streamer::PLATFORM_TWITCH
            ? 'Twitch channel login (lowercase). Found in the URL: twitch.tv/{login}.'
            : 'SOOP account user_id. Found in the broadcast URL after sooplive.com/.';
    }

    // ── Actions ───────────────────────────────────────

    public function setPlatform(string $platform): void
    {
        if (! in_array($platform, Streamer::PLATFORMS, true)) {
            return;
        }

        $this->platform = $platform;

        // Reset form state when switching tabs — values from one platform
        // would be confusing/invalid for the other.
        $this->reset(['newUserId', 'newLabel', 'newRace', 'editingId', 'editLabel', 'editRace']);
        $this->resetErrorBag();
    }

    public function add(): void
    {
        $data = $this->validate([
            'newUserId' => 'required|string|max:100',
            'newLabel'  => 'required|string|max:100',
            'newRace'   => 'nullable|in:,zerg,protoss,terran,random',
        ]);

        // Twitch logins are case-insensitive; normalize to lowercase so
        // lookups against the API's user_login (which is always lowercase)
        // match without surprises.
        $userId = $this->platform === Streamer::PLATFORM_TWITCH
            ? mb_strtolower($data['newUserId'])
            : $data['newUserId'];

        // Manual uniqueness check (scoped per-platform — matches DB unique index).
        // Done after normalization so "ZzzeroPL" vs "zzzeropl" collides correctly.
        $exists = Streamer::query()
            ->where('platform', $this->platform)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            $this->addError('newUserId', "This user_id is already whitelisted for {$this->platformTabs[$this->platform]}.");
            return;
        }

        Streamer::create([
            'platform' => $this->platform,
            'user_id'  => $userId,
            'label'    => $data['newLabel'],
            'race'     => $data['newRace'] ?: null,
        ]);

        $this->reset(['newUserId', 'newLabel', 'newRace']);
        $this->dispatch('streamer-added');
    }

    public function startEdit(int $id): void
    {
        $streamer = Streamer::findOrFail($id);

        // Defensive: ignore edits for rows not on the current tab.
        // Shouldn't happen with how the UI is wired, but we want to fail
        // closed if some race condition lands us here.
        if ($streamer->platform !== $this->platform) {
            return;
        }

        $this->editingId = $id;
        $this->editLabel = $streamer->label;
        $this->editRace  = $streamer->race;

        $this->resetErrorBag(['editLabel', 'editRace']);
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editLabel', 'editRace']);
        $this->resetErrorBag(['editLabel', 'editRace']);
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'editLabel' => 'required|string|max:100',
            'editRace'  => 'nullable|in:,zerg,protoss,terran,random',
        ]);

        Streamer::whereKey($this->editingId)
            ->where('platform', $this->platform) // safety scope
            ->update([
                'label' => $data['editLabel'],
                'race'  => $data['editRace'] ?: null,
            ]);

        $this->cancelEdit();
        $this->dispatch('streamer-updated');
    }

    public function delete(int $id): void
    {
        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        Streamer::whereKey($id)
            ->where('platform', $this->platform) // safety scope
            ->delete();

        $this->dispatch('streamer-deleted');
    }

    public function render()
    {
        return view('livewire.admin.streamers');
    }
}