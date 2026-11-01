<?php

namespace App\Livewire\Streams;

use App\Models\Streamer;
use App\Models\UserFavoriteStreamer;
use App\Services\Streams\LiveStreamsService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class Index extends Component
{
    public int $pollSeconds = 60;

    #[Url(as: 'tab', except: 'live')]
    public string $tab = 'live'; // live | channels

    #[Url(as: 'race', except: '')]
    public string $raceFilter = '';

    #[Url(as: 'platform', except: '')]
    public string $platformFilter = '';

    #[Url(as: 'sort', except: 'last_seen')]
    public string $channelSort = 'last_seen'; // last_seen | name

    #[Url(as: 'dir', except: 'desc')]
    public string $channelDir = 'desc'; // asc | desc

    public array $raceTabs = ['', 'terran', 'protoss', 'zerg', 'random'];

    public array $platformTabs = ['', LiveStreamsService::PLATFORM_SOOP, LiveStreamsService::PLATFORM_TWITCH];

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['live', 'channels'], true) ? $tab : 'live';
    }

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
            return $this->redirect(route('login'), navigate: false);
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
    public function channels(): array
    {
        // Only compute when actually viewing the Channels tab.
        if ($this->tab !== 'channels') {
            return [];
        }

        $query = Streamer::query();

        if ($this->platformFilter !== '') {
            $query->where('platform', $this->platformFilter);
        }

        if ($this->raceFilter !== '') {
            $query->where('race', $this->raceFilter);
        }

        $streamers = $query->get();

        // Build live lookup straight from cache — no aggregator, no extra DB queries.
        $soopCache   = Cache::get('soop:live:starcraft');
        $twitchCache = Cache::get('twitch:live:starcraft');

        $liveViewers = [];
        foreach (($soopCache['broadcasts'] ?? []) as $b) {
            $liveViewers['soop:' . ($b['user_id'] ?? '')] = (int) ($b['total_view_cnt'] ?? 0);
        }
        foreach (($twitchCache['streams'] ?? []) as $s) {
            $liveViewers['twitch:' . ($s['user_id'] ?? '')] = (int) ($s['viewers'] ?? 0);
        }

        $rows = $streamers->map(fn (Streamer $s) => [
            'platform'          => $s->platform,
            'user_id'           => $s->user_id,
            'label'             => $s->label,
            'race'              => $s->race,
            'last_seen_live_at' => $s->last_seen_live_at,
            'is_live'           => isset($liveViewers[$s->platform . ':' . $s->user_id]),
            'viewers'           => $liveViewers[$s->platform . ':' . $s->user_id] ?? null,
            'channel_url'       => $s->platform === 'twitch'
                ? "https://twitch.tv/{$s->user_id}"
                : "https://www.sooplive.com/station/{$s->user_id}",
        ])->all();

        // Sort: live always first (by viewers DESC), then offline by chosen column.
        usort($rows, function ($a, $b) {
            if ($a['is_live'] !== $b['is_live']) {
                return $b['is_live'] <=> $a['is_live'];
            }
            if ($a['is_live']) {
                return ($b['viewers'] ?? 0) <=> ($a['viewers'] ?? 0);
            }

            if ($this->channelSort === 'name') {
                $cmp = strcasecmp($a['label'] ?? $a['user_id'], $b['label'] ?? $b['user_id']);
            } else {
                if ($a['last_seen_live_at'] === null && $b['last_seen_live_at'] === null) return 0;
                if ($a['last_seen_live_at'] === null) return 1;
                if ($b['last_seen_live_at'] === null) return -1;
                $cmp = $a['last_seen_live_at'] <=> $b['last_seen_live_at'];
            }

            return $this->channelDir === 'asc' ? $cmp : -$cmp;
        });

        return $rows;
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

    public function setChannelSort(string $sort): void
    {
        if (! in_array($sort, ['last_seen', 'name'], true)) {
            return;
        }

        if ($this->channelSort === $sort) {
            $this->channelDir = $this->channelDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->channelSort = $sort;
            $this->channelDir = $sort === 'name' ? 'asc' : 'desc';
        }
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