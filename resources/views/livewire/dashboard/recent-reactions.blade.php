@use('Illuminate\Support\Str')
<div
    class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden"
    style="border-left: 4px solid #ec4899;"
>
    {{-- Header strip — same pattern as risers/fallers/streaks --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5 border-b border-zinc-700/50 bg-zinc-900/30">
        <p class="text-xs font-semibold uppercase tracking-widest flex items-center gap-1.5 min-w-0" style="color: #f472b6;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #ec4899; color: #0a0a0a;">😮</span>
            <span class="truncate">Last reactions</span>
        </p>
        <span class="text-xs text-zinc-500 font-mono shrink-0">recent</span>
    </div>

    {{-- Body --}}
    @if($this->reactions->isEmpty())
        <div class="px-3 sm:px-4 py-6 text-center">
            <p class="text-xs text-zinc-600">No reactions yet.</p>
        </div>
    @else
        <div class="flex flex-col divide-y divide-zinc-700/40">
            @foreach($this->reactions as $reaction)
                @php
                    $emoji = config('reactions.' . $reaction->type . '.emoji', '❓');
                    $url   = $this->urlFor($reaction);
                    $label = $this->labelFor($reaction);
                @endphp

                <div class="flex items-center gap-2 px-3 sm:px-4 py-2.5 hover:bg-zinc-700/20 transition-colors">

                    {{-- Emoji in a small tinted square — mirrors the badge pattern
                         from headers/chips elsewhere; gives it visual weight
                         without needing big text. --}}
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-md text-base leading-none shrink-0
                                 bg-pink-500/10 border border-pink-500/20">
                        {{ $emoji }}
                    </span>

                    {{-- User who reacted --}}
                    <span class="text-xs font-semibold text-zinc-200 shrink-0 truncate max-w-[80px]">
                        {{ $reaction->user?->name ?? '?' }}
                    </span>

                    <span class="text-[10px] uppercase tracking-wider text-zinc-600 font-semibold shrink-0">on</span>

                    {{-- What was reacted to --}}
                    @if($url)
                        <a href="{{ $url }}" wire:navigate
                           class="text-xs text-zinc-400 hover:text-zinc-200 hover:underline truncate flex-1 transition-colors">
                            {{ $label }}
                        </a>
                    @else
                        <span class="text-xs text-zinc-500 truncate flex-1">{{ $label }}</span>
                    @endif

                    {{-- Time-ago — mono zinc-600, like timestamps elsewhere --}}
                    <span class="text-xs text-zinc-600 font-mono shrink-0">
                        {{ $reaction->created_at->diffForHumans(null, true, true) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>