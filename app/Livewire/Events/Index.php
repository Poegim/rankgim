<?php

namespace App\Livewire\Events;

use App\Models\Event;
use App\Models\Player;
use App\Models\PlayerName;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $view = 'upcoming'; // upcoming | past | all

    #[Url]
    public string $typeFilter = 'all'; // all | stream | open

    public ?int $confirmingDeleteId = null;

    // ── Form state ────────────────────────────────────
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $description = '';
    public string $startsAt = '';
    public string $timezone = 'Europe/Warsaw';
    public bool $isOnline = true;
    public string $location = '';
    public array $links = [];
    public string $type = 'stream';

    // ── Player search state ───────────────────────────
    /** @var string Live search query for the player picker */
    public string $playerSearch = '';

    /** @var array<int, array{id: int, name: string, country_code: string, race: string}> */
    public array $selectedPlayers = [];

    // ── Computed ───────────────────────────────────────
    #[Computed]
    public function eventTypes(): array
    {
        return Event::TYPES;
    }

    #[Computed]
    public function events(): Collection
    {
        $query = Event::with(['user', 'players'])
            ->orderBy('starts_at', $this->view === 'past' ? 'desc' : 'asc');

        if ($this->view === 'upcoming') {
            $query->where('starts_at', '>=', now());
        } elseif ($this->view === 'past') {
            $query->where('starts_at', '<', now());
        }

        // Filter by event type
        if ($this->typeFilter !== 'all') {
            $query->where('type', $this->typeFilter);
        }

        return $query->get();
    }

    // Add to form state properties
    public array $predefinedLinksSelected = [];

    // Add new computed
    #[Computed]
    public function predefinedLinks(): array
    {
        return config('event_links', []);
    }

    // Add new action
    public function togglePredefinedLink(int $index): void
    {
        $link = $this->predefinedLinks[$index] ?? null;
        if (!$link) return;

        // Check if this url is already in links — if so, remove it
        $exists = collect($this->links)->search(fn($l) => $l['url'] === $link['url']);

        if ($exists !== false) {
            $this->removeLink($exists);
            unset($this->predefinedLinksSelected[$index]);
        } else {
            $this->links[] = [
                'type'  => $link['type'],
                'url'   => $link['url'],
                'label' => $link['label'],
            ];
            $this->predefinedLinksSelected[$index] = true;
        }
    }

    #[Computed]
    public function groupedEvents(): Collection
    {
        return $this->events->groupBy(function ($event) {
            // Use CET for grouping
            return $event->startsAtCET()->format('F Y');
        });
    }

    #[Computed]
    public function timezones(): array
    {
        return Event::TIMEZONES;
    }

    #[Computed]
    public function linkTypes(): array
    {
        return Event::LINK_TYPES;
    }

    #[Computed]
    public function recentEventNames(): array
    {
        return Event::latest('created_at')
            ->limit(10)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Live search results for the player picker — excludes already selected players.
     */
    #[Computed]
    public function playerResults(): Collection
    {
        if (strlen($this->playerSearch) < 2) {
            return collect();
        }

        $selectedIds = collect($this->selectedPlayers)->pluck('id')->toArray();

        $playerIds = PlayerName::where('name', 'like', '%' . $this->playerSearch . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id') // main players only, no aliases
            ->whereNotIn('id', $selectedIds)
            ->limit(8)
            ->get();
    }

    // ── Actions ───────────────────────────────────────

    public function delete(): void
    {
        if (!$this->confirmingDeleteId) {
            return;
        }

        $event = Event::findOrFail($this->confirmingDeleteId);

        $user = auth()->user();
        if (!$user || ($event->created_by !== $user->id && !$user->canManageGames())) {
            $this->confirmingDeleteId = null;
            return;
        }

        $event->delete();

        $this->confirmingDeleteId = null;

        unset($this->events, $this->groupedEvents);
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $event = Event::findOrFail($id);

        $user = auth()->user();
        if (!$user || ($event->created_by !== $user->id && !$user->canManageGames())) {
            return;
        }

        $this->resetForm();
        $this->predefinedLinksSelected = [];
        $this->editingId = $event->id;
        $this->name = $event->name;
        $this->type = $event->type;
        $this->description = $event->description ?? '';
        // Convert stored UTC to the event's timezone for the form
        $this->startsAt = $event->startsAtLocal()->format('Y-m-d\TH:i');
        $this->timezone = $event->timezone;
        $this->isOnline = $event->is_online;
        $this->location = $event->location ?? '';
        $this->links = $event->links ?? [];

        // Pre-populate selected players from existing relation
        $this->selectedPlayers = $event->players->map(fn (Player $p) => [
            'id'           => $p->id,
            'name'         => $p->name,
            'country_code' => $p->country_code,
            'race'         => $p->race,
        ])->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(Event::TYPES)),
            'description' => 'nullable|string|max:255',
            'startsAt' => 'required|date',
            'timezone' => 'required|string|in:' . implode(',', array_keys(Event::TIMEZONES)),
            'isOnline' => 'boolean',
            'location' => 'nullable|string|max:255',
            'links' => 'array|max:10',
            'links.*.url'  => 'nullable|url|max:500',
            'links.*.type' => 'nullable|string|in:' . implode(',', array_keys(Event::LINK_TYPES)),
            'links.*.label' => 'nullable|string|max:25',
            'selectedPlayers' => 'array|max:50',
            'selectedPlayers.*.id' => 'integer|exists:players,id',
        ]);

        $cleanLinks = collect($this->links)
            ->filter(fn ($link) => !empty($link['url']))
            ->values()
            ->toArray();

        // Parse the datetime in the selected timezone, then store as UTC
        $startsAtUtc = Carbon::parse($this->startsAt, $this->timezone)->utc();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description ?: null,
            'starts_at' => $startsAtUtc,
            'timezone' => $this->timezone,
            'is_online' => $this->isOnline,
            'location' => $this->isOnline ? null : ($this->location ?: null),
            'links' => $cleanLinks ?: null,
        ];

        $playerIds = collect($this->selectedPlayers)->pluck('id')->toArray();

        if ($this->editingId) {
            $event = Event::findOrFail($this->editingId);
            $user = auth()->user();
            if (!$user || ($event->created_by !== $user->id && !$user->canManageGames())) {
                return;
            }
            $event->update($data);
            $event->players()->sync($playerIds);
        } else {
            $data['created_by'] = auth()->id();
            $event = Event::create($data);
            $event->players()->sync($playerIds);
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->events, $this->groupedEvents);
    }

    /**
     * Add a player to the selected list by their ID.
     */
    public function addPlayer(int $playerId): void
    {
        // Prevent duplicates
        $alreadySelected = collect($this->selectedPlayers)->pluck('id')->contains($playerId);
        if ($alreadySelected) {
            $this->playerSearch = '';
            unset($this->playerResults);
            return;
        }

        $player = Player::findOrFail($playerId);

        $this->selectedPlayers[] = [
            'id'           => $player->id,
            'name'         => $player->name,
            'country_code' => $player->country_code,
            'race'         => $player->race,
        ];

        // Clear the search input after selecting
        $this->playerSearch = '';
        unset($this->playerResults);
    }

    /**
     * Remove a player from the selected list by their ID.
     */
    public function removePlayer(int $playerId): void
    {
        $this->selectedPlayers = collect($this->selectedPlayers)
            ->reject(fn ($p) => $p['id'] === $playerId)
            ->values()
            ->toArray();

        unset($this->playerResults);
    }

    public function addLink(): void
    {
        $this->links[] = ['type' => 'twitch', 'url' => '', 'label' => ''];
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
    }

    public function setView(string $view): void
    {
        $this->view = $view;
        unset($this->events, $this->groupedEvents);
    }

    public function setTypeFilter(string $type): void
    {
        $this->typeFilter = $type;
        unset($this->events, $this->groupedEvents);
    }

    private function resetForm(): void
    {
        $this->predefinedLinksSelected = [];
        $this->editingId = null;
        $this->type = 'stream';
        $this->name = '';
        $this->description = '';
        $this->startsAt = now()->format('Y-m') . '-01T00:00';
        $this->timezone = 'Europe/Warsaw';
        $this->isOnline = true;
        $this->location = '';
        $this->links = [];
        $this->selectedPlayers = [];
        $this->playerSearch = '';
        unset($this->playerResults);
    }

    public function render()
    {
        return view('livewire.events.index');
    }
}