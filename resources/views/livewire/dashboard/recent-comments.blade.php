@use('Illuminate\Support\Str')
<div class="rounded-lg overflow-hidden
            border border-travertine-300 dark:border-zinc-700/60
            bg-travertine-50 dark:bg-zinc-800/40"
     style="border-left: 4px solid #14b8a6;">

    {{-- Header strip — teal accent, semantic = discussion/chat --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #0f766e;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #14b8a6; color: #f0fdfa;">💬</span>
            <span class="truncate">Last comments</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">recent</span>
    </div>

    {{-- Body --}}
    @if($this->comments->isEmpty())
        <div class="px-3 sm:px-4 py-10 text-center">
            <p class="text-sm text-travertine-500 dark:text-zinc-500">
                No comments yet — start the discussion!
            </p>
        </div>
    @else
        <div class="flex flex-col">
            @foreach($this->comments as $comment)
                @php
                    // Adjust accessors to match your Comment model.
                    $user    = $comment->user;
                    $body    = $comment->body ?? $comment->content ?? '';
                    $preview = Str::limit($body, 80);
                    $url     = method_exists($this, 'urlFor') ? $this->urlFor($comment) : null;
                    $label   = method_exists($this, 'labelFor') ? $this->labelFor($comment) : null;
                @endphp

                <div class="flex items-start gap-3 px-3 sm:px-4 py-3 transition-colors
                            border-b border-travertine-350 last:border-b-0
                            dark:border-zinc-700/40
                            hover:bg-oxblood/5 dark:hover:bg-zinc-700/20">

                    {{-- Avatar with chat emoji badge overlay --}}
                    <div class="relative shrink-0">
                        <flux:avatar
                            size="lg"
                            :src="$user?->profilePhotoUrl()"
                            :name="$user?->name ?? '?'"
                            color="auto"
                            :color:seed="$user?->id ?? 0"
                        />
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center text-sm leading-none shadow-sm
                                     bg-travertine-50 ring-2 ring-travertine-50
                                     dark:bg-zinc-900 dark:ring-zinc-900">
                            💬
                        </span>
                    </div>

                    {{-- Comment narrative + preview --}}
                    <div class="flex-1 min-w-0 leading-snug">
                        {{-- Header line: "X commented on Y" + time on the right --}}
                        <div class="flex items-baseline gap-1.5 flex-wrap text-sm
                                    text-travertine-800 dark:text-zinc-200">
                            <span class="font-bold
                                         text-travertine-900 dark:text-white">
                                {{ $user?->name ?? '?' }}
                            </span>
                            @if($label && $url)
                                <span class="text-travertine-600 dark:text-zinc-400">on</span>
                                <a href="{{ $url }}" wire:navigate
                                   class="font-semibold transition-colors
                                          text-oxblood hover:text-oxblood-deep
                                          dark:text-teal-400 dark:hover:text-teal-300"
                                   style="border-bottom: 1px dotted currentColor;">
                                    {{ $label }}
                                </a>
                            @endif
                            <span class="text-xs font-mono shrink-0 ml-auto
                                         text-travertine-500 dark:text-zinc-500">
                                {{ $comment->created_at->diffForHumans(null, true, true) }}
                            </span>
                        </div>

                        {{-- Comment body preview — italic quote --}}
                        @if($preview)
                            <p class="text-sm mt-1 italic leading-relaxed
                                      text-travertine-700 dark:text-zinc-300">
                                "{{ $preview }}"
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>