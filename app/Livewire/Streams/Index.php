<?php

namespace App\Livewire\Streams;

use App\Models\UserFavoriteStreamer;
use App\Services\Streams\LiveStreamsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    public int $pollSeconds = 60;

    #[Url(as: 'race', except: '')]
    public string $raceFilter = '';

    #[Url(as: 'platform', except: '')]
    public string $platformFilter = '';

    public array $raceTabs = ['', 'terran', 'protoss', 'zerg', 'random'];

    public array $platformTabs = ['', LiveStreamsService::PLATFORM_SOOP, LiveStreamsService::PLATFORM_TWITCH];

    public function setRace(string $race): void
    {
        $this->raceFilter = in_array($race, $this->raceTabs, true) ? $race : '';
    }

    public function setPlatform(string $platform): void
    {
        $this->platformFilter = in_array($platform, $this->platformTabs, true) ? $platform : '';
    }

    /**
     * Toggle a favorite for the currently-authenticated user.
     *
     * Guests get redirected to login. The intended URL (with current filters)
     * is preserved so they land back on the same view post-login.
     *
     * @return mixed Redirect for guests; null when toggled.
     */
    public function toggleFavorite(string $platform, string $userId)
    {
        if (! auth()->check()) {
            // Preserve current URL (filters included) so post-login lands here.
            session()->put('url.intended', request()->fullUrl());

            return redirect()->route('login');
        }

        if (! in_array($platform, [LiveStreamsService::PLATFORM_SOOP, LiveStreamsService::PLATFORM_TWITCH], true)) {
            return null;
        }

        if ($userId === '') {
            return null;
        }

        $existing = UserFavoriteStreamer::query()
            ->where('user_id', auth()->id())
            ->where('platform', $platform)
            ->where('streamer_user_id', $userId)
            ->first();

        if ($existing !== null) {
            $existing->delete();
        } else {
            UserFavoriteStreamer::create([
                'user_id'          => auth()->id(),
                'platform'         => $platform,
                'streamer_user_id' => $userId,
            ]);
        }

        // No explicit return — Livewire will re-render the page and the computed
        // properties below will pick up the new favorite state.
        return null;
    }

    /**
     * Admin-only: add a non-whitelisted streamer to the Featured whitelist
     * directly from the streams page. Defaults: label = user_nick, race = null.
     *
     * Admin can refine label/race afterwards via /admin/streamers.
     */
    public function addToFeatured(string $platform, string $userId, string $label, ?string $race = null): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);

        if (! in_array($platform, [LiveStreamsService::PLATFORM_SOOP, LiveStreamsService::PLATFORM_TWITCH], true)) {
            return;
        }

        $userId = $platform === LiveStreamsService::PLATFORM_TWITCH
            ? mb_strtolower(trim($userId))
            : trim($userId);

        $label = trim($label);
        if ($userId === '' || $label === '') {
            return;
        }

        $allowedRaces = ['zerg', 'protoss', 'terran', 'random'];
        $race = in_array($race, $allowedRaces, true) ? $race : null;

        \App\Models\Streamer::firstOrCreate(
            ['platform' => $platform, 'user_id' => $userId],
            ['label' => $label, 'race' => $race],
        );

        // Reset the inline mini-form for this card (handled client-side via Alpine).
        $this->dispatch('streamer-added-from-card', platform: $platform, userId: $userId);
    }

    /**
     * Admin-only: remove a streamer from the Featured whitelist directly from
     * the streams page. Card will fall back into "Other" on next render.
     */
    public function removeFromFeatured(string $platform, string $userId): void
    {
        abort_if(! auth()->user()?->isAdmin(), 403);

        if (! in_array($platform, [LiveStreamsService::PLATFORM_SOOP, LiveStreamsService::PLATFORM_TWITCH], true)) {
            return;
        }

        $userId = $platform === LiveStreamsService::PLATFORM_TWITCH
            ? mb_strtolower(trim($userId))
            : trim($userId);

        \App\Models\Streamer::where('platform', $platform)
            ->where('user_id', $userId)
            ->delete();

        $this->dispatch('streamer-removed-from-card', platform: $platform, userId: $userId);
    }

    #[Computed]
    public function featured(): array
    {
        $platform = $this->platformFilter === '' ? null : $this->platformFilter;
        $streams  = app(LiveStreamsService::class)->featuredStreams($platform, auth()->user());

        return $this->applyRaceFilter($streams);
    }

    #[Computed]
    public function others(): array
    {
        $platform = $this->platformFilter === '' ? null : $this->platformFilter;
        $streams  = app(LiveStreamsService::class)->otherStreams($platform, auth()->user());

        return $this->applyRaceFilter($streams);
    }

    #[Computed]
    public function lastFetchedAt(): ?\Illuminate\Support\Carbon
    {
        return app(LiveStreamsService::class)->lastFetchedAt();
    }

    #[Computed]
    public function isStale(): bool
    {
        return app(LiveStreamsService::class)->isStale();
    }

    /**
     * Filter a stream list by the currently selected race.
     * Empty filter passes everything through.
     */
    protected function applyRaceFilter(array $streams): array
    {
        if ($this->raceFilter === '') {
            return $streams;
        }

        return array_values(array_filter(
            $streams,
            fn ($s) => ($s['race'] ?? null) === $this->raceFilter,
        ));
    }

    public function render()
    {
        return view('livewire.streams.index');
    }
}