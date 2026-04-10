<div class="flex flex-col gap-4">

    {{-- Upcoming Events --}}
    @if($this->upcomingEvents->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-zinc-500 dark:text-zinc-400">📅 Upcoming Events</h2>
            <a href="{{ route('events.index') }}" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors" wire:navigate>
                View all →
            </a>
        </div>
        <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach($this->upcomingEvents as $event)
            <div class="flex items-center justify-between py-2.5 gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        {{-- Type badge --}}
                        @if($event->isStream())
                        <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-500/15 text-purple-300 border border-purple-500/25">
                            Stream
                        </span>
                        @else
                        <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-amber-500/15 text-amber-300 border border-amber-500/25">
                            Open
                        </span>
                        @endif
                        <p class="text-sm font-semibold text-zinc-800 dark:text-white truncate">{{ $event->name }}</p>
                    </div>

                    @if($event->description)
                    <p class="text-xs text-zinc-500 truncate mt-0.5">{{ $event->description }}</p>
                    @endif

                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @php $links = $event->parsedLinks(); @endphp
                        @if($links)
                            @foreach($links as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium transition-opacity hover:opacity-80"
                                style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}40">
                                {{ $link['label'] ?: ucfirst($link['type']) }}
                            </a>
                            @endforeach
                        @endif
                        @if($event->isRegistrationOpen())
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-xs font-medium
                            bg-green-500/15 text-green-400 border border-green-500/25 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"></span>
                            Registration open
                        </span>
                        @endif
                    </div>
                </div>
                <div class="shrink-0 text-right">
                    <p class="{{ $event->isStream() ? 'text-purple-300' : 'text-amber-400' }} text-base font-mono font-bold tabular-nums"
                        x-data="{
                            target: {{ $event->starts_at->timestamp }},
                            d: 0, h: 0, m: 0, s: 0,
                            init() { this.tick(); setInterval(() => this.tick(), 1000); },
                            tick() {
                                const diff = this.target - Math.floor(Date.now() / 1000);
                                if (diff <= 0) { this.d = this.h = this.m = this.s = 0; return; }
                                this.d = Math.floor(diff / 86400);
                                this.h = Math.floor((diff % 86400) / 3600);
                                this.m = Math.floor((diff % 3600) / 60);
                                this.s = diff % 60;
                            }
                        }">
                        <span x-show="d > 0" x-text="d + 'd '"></span><span x-text="String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
                    </p>
                    <p class="text-xs font-mono text-zinc-500 mt-0.5">
                        {{ $event->startsAtCET()->format('d M, H:i') }} CET
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Top 10 ranking --}}
    <livewire:dashboard.top-players />

    {{-- Race matchups --}}
    <livewire:dashboard.race-matchups />

    {{-- Recent achievements --}}
    <div class="md:col-span-2">
        <livewire:recent-achievements />
    </div>

    {{-- Games & active players per year charts --}}
    <livewire:dashboard.yearly-charts />

    {{-- Recent games + Recent tournaments --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <livewire:dashboard.recent-games />
        <livewire:dashboard.recent-tournaments />
    </div>

    {{-- Spread chart --}}
    <livewire:dashboard.spread-chart />

    {{-- Show more interesting stats — components inside x-if are not mounted until button is clicked --}}
    <div x-data="{ open: false }">

        <button
            x-show="!open"
            x-on:click="open = true"
            class="w-full py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-white hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors"
        >
            Show more interesting stats ↓
        </button>

        {{-- Components are only mounted in DOM after clicking the button --}}
        <template x-if="open">
            <div class="flex flex-col gap-4 mt-4">

                {{-- Risers + Fallers + Hot streaks --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <livewire:dashboard.risers-and-fallers />
                    </div>
                    <livewire:dashboard.hot-streaks />
                </div>

                

                {{-- Most active + Biggest upsets + Most dominant --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <livewire:dashboard.most-active />
                    <livewire:dashboard.biggest-upsets />
                    <livewire:dashboard.most-dominant />
                </div>

                {{-- Top rivalries --}}
                <livewire:dashboard.top-rivalries />

            </div>
        </template>

    </div>

</div>