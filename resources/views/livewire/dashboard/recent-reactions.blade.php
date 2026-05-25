@use('Illuminate\Support\Str')
<div class="rounded-lg overflow-hidden
            border border-travertine-300 dark:border-zinc-700/60
            bg-travertine-50 dark:bg-zinc-800/40"
     style="border-left: 4px solid #ec4899;">

    {{-- Header strip — pink accent, semantic = social/heart/reactions --}}
    <div class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2.5
                border-b border-travertine-300 dark:border-zinc-700/50
                bg-travertine-75 dark:bg-zinc-900/30">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] flex items-center gap-1.5 min-w-0"
           style="color: #be185d;">
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md text-[11px] shrink-0"
                  style="background: #ec4899; color: #fdf2f8;">😮</span>
            <span class="truncate">Last reactions</span>
        </p>
        <span class="text-xs font-mono shrink-0
                     text-travertine-500 dark:text-zinc-500">recent</span>
    </div>

    {{-- Body --}}
    @if($this->reactions->isEmpty())
        <div class="px-3 sm:px-4 py-10 text-center">
            <p class="text-sm text-travertine-500 dark:text-zinc-500">
                No reactions yet — be the first to react!
            </p>
        </div>
    @else
        <div class="flex flex-col">
            @foreach($this->reactions as $reaction)
                @php
                    $emoji = config('reactions.' . $reaction->type . '.emoji', '❓');
                    $url   = $this->urlFor($reaction);
                    $label = $this->labelFor($reaction);
                    $user  = $reaction->user;
                @endphp

                <div class="flex items-center gap-3 px-3 sm:px-4 py-3 transition-colors
                            border-b border-travertine-350 last:border-b-0
                            dark:border-zinc-700/40
                            hover:bg-oxblood/5 dark:hover:bg-zinc-700/20">

                    {{-- Avatar with emoji badge overlay.                                  --}}
                    {{-- Flux handles photo + deterministic color fallback via :color:seed.--}}
                    {{-- The emoji badge is positioned absolutely on the bottom-right of   --}}
                    {{-- the avatar wrapper — clipped like Discord/Polymarket activity.    --}}
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
                            {{ $emoji }}
                        </span>
                    </div>

                    {{-- Narrative sentence: "X reacted to Y" --}}
                    <div class="flex-1 min-w-0 text-sm leading-snug
                                text-travertine-800 dark:text-zinc-200">
                        <span class="font-bold
                                     text-travertine-900 dark:text-white">
                            {{ $user?->name ?? '?' }}
                        </span>
                        <span class="text-travertine-600 dark:text-zinc-400">
                            reacted to
                        </span>
                        @if($url)
                            <a href="{{ $url }}" wire:navigate
                               class="font-semibold transition-colors inline
                                      text-oxblood hover:text-oxblood-deep
                                      dark:text-rose-400 dark:hover:text-rose-300"
                               style="border-bottom: 1px dotted currentColor;">
                                {{ $label }}
                            </a>
                        @else
                            <span class="font-semibold text-travertine-700 dark:text-zinc-300">
                                {{ $label }}
                            </span>
                        @endif
                    </div>

                    {{-- Time-ago — short form, monospaced --}}
                    <span class="text-xs font-mono shrink-0
                                 text-travertine-500 dark:text-zinc-500">
                        {{ $reaction->created_at->diffForHumans(null, true, true) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>