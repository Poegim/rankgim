@use('Illuminate\Support\Str')
<div
    class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden"
    style="border-left: 4px solid #06b6d4;"
>
    {{-- Header strip — same pattern as recent-reactions --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5 border-b border-zinc-700/50 bg-zinc-900/30">
        <p class="text-xs font-semibold uppercase tracking-widest flex items-center gap-1.5 min-w-0" style="color: #22d3ee;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #06b6d4; color: #0a0a0a;">💬</span>
            <span class="truncate">Last comments</span>
        </p>
        <span class="text-xs text-zinc-500 font-mono shrink-0">recent</span>
    </div>

    {{-- Body --}}
    @if($this->comments->isEmpty())
        <div class="px-3 sm:px-4 py-6 text-center">
            <p class="text-xs text-zinc-600">No comments yet.</p>
        </div>
    @else
        <div class="flex flex-col divide-y divide-zinc-700/40">
            @foreach($this->comments as $comment)
                @php
                    $url   = $this->urlFor($comment);
                    $label = $this->labelFor($comment);
                @endphp

                <div class="flex items-center gap-2 px-3 sm:px-4 py-2.5 hover:bg-zinc-700/20 transition-colors min-w-0">

                    {{-- User who commented --}}
                    <span class="text-xs font-semibold text-zinc-200 shrink-0 truncate max-w-[80px]">
                        {{ $comment->user?->name ?? '?' }}
                    </span>

                    <span class="text-[10px] uppercase tracking-wider text-zinc-600 font-semibold shrink-0">on</span>

                    {{-- What was commented on --}}
                    @if($url)
                        <a href="{{ $url }}" wire:navigate
                           class="text-xs text-zinc-400 hover:text-zinc-200 hover:underline shrink-0 truncate max-w-[100px] transition-colors">
                            {{ $label }}
                        </a>
                    @else
                        <span class="text-xs text-zinc-500 shrink-0 truncate max-w-[100px]">{{ $label }}</span>
                    @endif

                    {{-- Comment body preview — italic to differentiate from labels --}}
                    <span class="text-xs text-zinc-500 truncate flex-1 italic">
                        — {{ Str::limit($comment->body, 40) }}
                    </span>

                    {{-- Time-ago — mono zinc-600, like timestamps elsewhere --}}
                    <span class="text-xs text-zinc-600 font-mono shrink-0">
                        {{ $comment->created_at->diffForHumans(null, true, true) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>