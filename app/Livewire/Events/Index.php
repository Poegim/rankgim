<?php

namespace App\Livewire\Events;

use App\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $view = 'upcoming'; // upcoming | past | all

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
    

    // ── Computed ───────────────────────────────────────

    #[Computed]
    public function events(): Collection
    {
        $query = Event::with('user')
            ->orderBy('starts_at', $this->view === 'past' ? 'desc' : 'asc');

        if ($this->view === 'upcoming') {
            $query->where('starts_at', '>=', now());
        } elseif ($this->view === 'past') {
            $query->where('starts_at', '<', now());
        }

        return $query->get();
    }

    #[Computed]
    public function groupedEvents(): Collection
    {
        return $this->events->groupBy(function ($event) {
            // use CET for grouping
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

    // ── Actions ───────────────────────────────────────

    public function delete(): void
    {
        if (!$this->confirmingDeleteId) {
            return;
        }

        $event = Event::findOrFail($this->confirmingDeleteId);

        $user = auth()->user();
        if (!$user || ($event->created_by !== $user->id && !$user->canManageGames())) {
                $this->confirmingDeleteId = null;  // ← ADD THIS
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

        $this->editingId = $event->id;
        $this->name = $event->name;
        $this->description = $event->description ?? '';
        // Convert stored UTC to the event's timezone for the form
        $this->startsAt = $event->startsAtLocal()->format('Y-m-d\TH:i');
        $this->timezone = $event->timezone;
        $this->isOnline = $event->is_online;
        $this->location = $event->location ?? '';
        $this->links = $event->links ?? [];

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'startsAt' => 'required|date',
            'timezone' => 'required|string|in:' . implode(',', array_keys(Event::TIMEZONES)),
            'isOnline' => 'boolean',
            'location' => 'nullable|string|max:255',
            'links' => 'array|max:10',
            'links.*.type' => 'required|string|in:' . implode(',', array_keys(Event::LINK_TYPES)),
            'links.*.url' => 'required|url|max:500',
            'links.*.label' => 'nullable|string|max:100',
        ]);

        $cleanLinks = collect($this->links)
            ->filter(fn ($link) => !empty($link['url']))
            ->values()
            ->toArray();

        // Parse the datetime in the selected timezone, then store as UTC
        $startsAtUtc = Carbon::parse($this->startsAt, $this->timezone)->utc();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'starts_at' => $startsAtUtc,
            'timezone' => $this->timezone,
            'is_online' => $this->isOnline,
            'location' => $this->isOnline ? null : ($this->location ?: null),
            'links' => $cleanLinks ?: null,
        ];

        if ($this->editingId) {
            $event = Event::findOrFail($this->editingId);
            $user = auth()->user();
            if (!$user || ($event->created_by !== $user->id && !$user->canManageGames())) {
                return;
            }
            $event->update($data);
        } else {
            $data['created_by'] = auth()->id();
            Event::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->events, $this->groupedEvents);
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

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->startsAt = '';
        $this->timezone = 'Europe/Warsaw';
        $this->isOnline = true;
        $this->location = '';
        $this->links = [];
    }

    public function render()
    {
        return view('livewire.events.index');
    }
}