{{--
    Single live stream card (full /streams page version).

    Layout: Option C — Hybrid with race accent bar.
    - Letterbox 21:9 thumbnail on top.
    - Platform badge top-left, viewer pill bottom-left.
    - Top-right: favorite star + (admin) add/remove featured button.
    - Bottom of card: inline expanding mini-form for admin "Add to Featured" flow.

    Props:
      $s         array — stream row (incl. platform, is_favorite, etc.)
      $showLabel bool  — true on Featured cards, false on Other
--}}

@php
    // Platform badge metadata.
    $platform = $s['platform'] ?? 'soop';
    $platformBadge = match ($platform) {
        'twitch' => ['label' => 'Twitch', 'bg' => '#9146ff'],
        'soop'   => ['label' => 'SOOP',   'bg' => '#ef4444'],
        default  => ['label' => strtoupper($platform), 'bg' => '#52525b'],
    };

    $race = $s['race'] ?? null;
    $accentColor = $race
        ? "var(--color-race-{$race})"
        : 'rgb(161, 161, 170)';

    $primaryName = $showLabel && ! empty($s['label']) ? $s['label'] : $s['user_nick'];

    // Is the current user an admin? Cheap call — Livewire renders this per card,
    // but `auth()->user()` is memoized within the request, so the cost is one
    // method call per render, not one DB hit per card.
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp

    <div
        class="group flex flex-col overflow-hidden rounded-lg border border-zinc-700/60 bg-zinc-900/70 transition hover:border-rose-500/60 hover:bg-zinc-900"
        x-data="{
            addFormOpen: false,
            {{-- Default label = user_id (ASCII slug) instead of user_nick (often localized).
                 Admin can override before hitting Save. Example: Twitch 'platesports' instead of '플레이트스포츠'. --}}
            newLabel: '{{ addslashes($s['user_id']) }}',
            newRace: '',
        }"
    >
    {{-- Outer link — uses contents display so child clicks (star, admin buttons) don't trigger navigation --}}
    <a href="{{ $s['play_url'] }}"
       target="_blank"
       rel="noopener noreferrer"
       class="flex flex-col"
    >
        {{-- ─── Thumbnail block ───────────────────────────── --}}
        <div class="relative w-full overflow-hidden bg-zinc-950" style="aspect-ratio: 21 / 9;">
            @if ($s['thumbnail'])
                <img
                    src="{{ $s['thumbnail'] }}"
                    alt=""
                    class="h-full w-full object-cover transition group-hover:scale-[1.02]"
                    loading="lazy"
                >
            @else
                <div class="flex h-full w-full items-center justify-center"
                     style="background: color-mix(in srgb, {{ $accentColor }} 20%, rgb(24, 24, 27));">
                    <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                        No preview
                    </span>
                </div>
            @endif

            {{-- Platform badge --}}
            <span
                class="absolute left-2 top-2 rounded px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-lg"
                style="background: {{ $platformBadge['bg'] }};"
            >
                {{ $platformBadge['label'] }}
            </span>

            {{-- Top-right cluster: admin button (if any) + star --}}
            <div class="absolute right-2 top-2 flex items-center gap-1">

                {{-- Admin: Add to Featured (only on Other-section cards) --}}
                @if ($isAdmin && ! $showLabel)
                    <button
                        type="button"
                        title="Add to Featured"
                        @click.stop.prevent="addFormOpen = !addFormOpen"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-950/90 text-emerald-300 shadow-lg transition hover:scale-110 hover:text-emerald-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                @endif

                {{-- Admin: Remove from Featured (only on Featured-section cards) --}}
                @if ($isAdmin && $showLabel)
                    <button
                        type="button"
                        title="Remove from Featured"
                        wire:click.stop.prevent="removeFromFeatured('{{ $s['platform'] }}', '{{ $s['user_id'] }}')"
                        wire:confirm="Remove {{ $s['label'] ?: $s['user_nick'] }} from Featured?"
                        @click.stop
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-950/90 text-rose-300 shadow-lg transition hover:scale-110 hover:text-rose-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                    </button>
                @endif

                


                    {{-- Favorite star (logged-in users + guests) --}}
                    @php
                        $isFav    = $s['is_favorite'] ?? false;
                        $favTitle = auth()->check()
                            ? ($isFav ? 'Remove from favorites' : 'Add to favorites')
                            : 'Log in to favorite this streamer';
                    @endphp
                    <button
                        type="button"
                        wire:click.stop.prevent="toggleFavorite('{{ $s['platform'] }}', '{{ $s['user_id'] }}')"
                        @click.stop
                        title="{{ $favTitle }}"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-zinc-950/90 shadow-lg transition hover:scale-110 {{ $isFav ? 'text-amber-400' : 'text-amber-300/70 hover:text-amber-300' }}"
                    >
                        @if ($isFav)
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 drop-shadow">
                                <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        @endif
                    </button>
                
            </div>

            {{-- Bottom-left: live viewer pill --}}
            <span class="absolute bottom-2 left-2 inline-flex items-center gap-1.5 rounded bg-zinc-950/90 px-2 py-1 text-xs font-bold text-zinc-50 shadow-lg">
                <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-rose-500"></span>
                {{ number_format($s['viewers']) }}
            </span>
        </div>

        {{-- ─── Text block ────────────────────────────────── --}}
        <div class="flex gap-3 p-3" style="border-left: 4px solid {{ $accentColor }};">
            <div class="min-w-0 flex-1 space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="text-base font-bold leading-tight"
                        style="color: {{ $accentColor }};"
                    >
                        {{ $primaryName }}
                    </span>

                    @if ($race)
                        <span
                            class="shrink-0 rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow"
                            style="background: {{ $accentColor }};"
                        >
                            {{ $race }}
                        </span>
                    @endif
                </div>

                @if ($showLabel && ! empty($s['label']) && $s['label'] !== $s['user_nick'])
                    <p class="truncate text-[11px] text-zinc-500">
                        aka {{ $s['user_nick'] }}
                    </p>
                @endif

                <p class="text-xs leading-snug text-zinc-300" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $s['broad_title'] }}
                </p>

                @if ($s['started_at'])
                    <p class="text-[11px] text-zinc-500">
                        started {{ $s['started_at']->diffForHumans() }}
                    </p>
                @endif
            </div>
        </div>
    </a>

    {{-- ─── Admin inline "Add to Featured" mini-form ───────────────── --}}
    {{-- Lives OUTSIDE the <a> wrapper above so its inputs don't trigger navigation. --}}
    @if ($isAdmin && ! $showLabel)
        <div
            x-show="addFormOpen"
            x-transition.duration.150ms
            x-cloak
            class="border-t border-zinc-700/60 bg-zinc-950/40 p-3 space-y-2"
        >
            <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">
                Add to Featured
            </p>
            <div class="grid grid-cols-[1fr_auto] gap-2">
                <input
                    type="text"
                    x-model="newLabel"
                    placeholder="Label"
                    class="rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-zinc-100 placeholder-zinc-500 focus:border-emerald-500 focus:outline-none"
                >
                <select
                    x-model="newRace"
                    class="rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-xs text-zinc-100 focus:border-emerald-500 focus:outline-none"
                >
                    <option value="">— race —</option>
                    <option value="terran">terran</option>
                    <option value="protoss">protoss</option>
                    <option value="zerg">zerg</option>
                    <option value="random">random</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    @click="addFormOpen = false"
                    class="rounded px-2 py-1 text-[11px] text-zinc-400 hover:text-zinc-200"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="$wire.addToFeatured('{{ $s['platform'] }}', '{{ $s['user_id'] }}', newLabel, newRace || null); addFormOpen = false"
                    class="rounded bg-emerald-600 px-3 py-1 text-[11px] font-semibold text-white hover:bg-emerald-500"
                >
                    Save
                </button>
            </div>
        </div>
    @endif
</div>