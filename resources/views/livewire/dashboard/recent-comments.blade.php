<div class="rounded-xl border border-zinc-700/60 bg-zinc-900/40 p-5">
    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-4">💬 Last comments</p>

    @if($this->comments->isEmpty())
        <p class="text-xs text-zinc-600 text-center py-2">No comments yet.</p>
    @else
        <div class="flex flex-col divide-y divide-zinc-700/50">
            @foreach($this->comments as $comment)
                @php
                    $url   = $this->urlFor($comment);
                    $label = $this->labelFor($comment);
                @endphp
                <div class="flex items-center gap-2 py-2 min-w-0">
                    <span class="text-xs font-medium text-zinc-300 shrink-0 truncate max-w-[70px]">{{ $comment->user?->name ?? '?' }}</span>
                    <span class="text-xs text-zinc-600 shrink-0">on</span>
                    @if($url)
                        <a href="{{ $url }}" wire:navigate class="text-xs text-zinc-400 hover:text-zinc-200 shrink-0 transition-colors">{{ $label }}</a>
                    @else
                        <span class="text-xs text-zinc-500 shrink-0">{{ $label }}</span>
                    @endif
                    <span class="text-xs text-zinc-600 truncate flex-1 italic">— {{ \Illuminate\Support\Str::limit($comment->body, 40) }}</span>
                    <span class="text-xs text-zinc-700 font-mono shrink-0">{{ $comment->created_at->diffForHumans(null, true, true) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>