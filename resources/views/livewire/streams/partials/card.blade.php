{{--
    Single live stream card.

    Props:
      $s         array — stream row (user_id, user_nick, broad_title, viewers, thumbnail, started_at, play_url, plus label/race for whitelisted streams)
      $showLabel bool  — when true, render the whitelist label + race tag (Featured section). When false, fall back to user_nick (Other section).
--}}

    <a href="{{ $s['play_url'] }}"
    target="_blank"
    rel="noopener noreferrer"
    class="flex items-start gap-3 rounded-lg border border-zinc-700/60 bg-zinc-800/40 p-3 transition hover:border-rose-500/60 hover:bg-zinc-800/70"
>
    @if ($s['thumbnail'])
        <img
            src="{{ $s['thumbnail'] }}"
            alt=""
            class="block h-20 w-36 shrink-0 rounded object-cover"
            loading="lazy"
        >
    @endif

    <div class="min-w-0 flex-1 space-y-1">
        {{-- Row 1: identity + race tag --}}
        <div class="flex items-center gap-2">
            @if ($showLabel)
                <span class="truncate text-sm font-semibold text-zinc-100">
                    {{ $s['label'] }}
                </span>
                @if (! empty($s['race']))
                    <span
                        class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                        style="background: color-mix(in srgb, var(--color-race-{{ $s['race'] }}) 20%, transparent); color: var(--color-race-{{ $s['race'] }});"
                    >
                        {{ ucfirst($s['race']) }}
                    </span>
                @endif
                <span class="truncate text-xs text-zinc-500">{{ $s['user_nick'] }}</span>
            @else
                {{-- Non-whitelisted: nick is the only identity we have --}}
                <span class="truncate text-sm font-semibold text-zinc-100">
                    {{ $s['user_nick'] }}
                </span>
            @endif
        </div>

        {{-- Row 2: broadcast title --}}
        <p class="truncate text-sm text-zinc-300">
            {{ $s['broad_title'] }}
        </p>

        {{-- Row 3: meta --}}
        <p class="text-xs text-zinc-500">
            👥 {{ number_format($s['viewers']) }}
            @if ($s['started_at'])
                · {{ $s['started_at']->diffForHumans() }}
            @endif
        </p>
    </div>
</a>