<div class="rounded-xl border border-zinc-700/60 bg-zinc-900/40 p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">😮 Last reactions</p>

    @if($this->reactions->isEmpty())
        <p class="text-xs text-zinc-600 text-center py-2">No reactions yet.</p>
    @else
        <div class="flex flex-col divide-y divide-zinc-700/50">
            @foreach($this->reactions as $reaction)
                @php
                    $emoji = config('reactions.' . $reaction->type . '.emoji', '❓');
                    $url   = $this->urlFor($reaction);
                    $label = $this->labelFor($reaction);
                @endphp
                <div class="flex items-center gap-2 py-2">
                    <span class="text-base leading-none shrink-0">{{ $emoji }}</span>
                    <span class="text-xs font-medium text-zinc-300 shrink-0 truncate max-w-[70px]">{{ $reaction->user?->name ?? '?' }}</span>
                    <span class="text-xs text-zinc-600 shrink-0">on</span>
                    @if($url)
                        <a href="{{ $url }}" wire:navigate class="text-xs text-zinc-400 hover:text-zinc-200 truncate flex-1 transition-colors">{{ $label }}</a>
                    @else
                        <span class="text-xs text-zinc-500 truncate flex-1">{{ $label }}</span>
                    @endif
                    <span class="text-xs text-zinc-700 font-mono shrink-0">{{ $reaction->created_at->diffForHumans(null, true, true) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>