<div class="flex flex-col gap-4">

    {{-- Upcoming events — only rendered when there are events --}}
    {{-- <livewire:events.upcoming /> --}}

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
                    <p class="text-sm font-semibold text-zinc-800 dark:text-white truncate">{{ $event->name }}</p>
                    @if($event->description)
                    <p class="text-xs text-zinc-500 truncate mt-0.5">{{ $event->description }}</p>
                    @endif
                </div>
        <div class="shrink-0 text-right">
            <p class="text-base font-mono font-bold text-amber-400 tabular-nums"
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

    <div class="md:col-span-2">
        <livewire:recent-achievements />
    </div>

    {{-- Games & active players per year charts --}}
    <livewire:dashboard.yearly-charts />

    {{-- Recent games + Recent achievements (full width) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <livewire:dashboard.recent-games />
        <livewire:dashboard.recent-tournaments />
    </div>
    

    

    {{-- Risers + Fallers + Hot streaks --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <livewire:dashboard.risers-and-fallers />
        </div>
        <livewire:dashboard.hot-streaks />
    </div>

    {{-- Spread chart --}}
    <livewire:dashboard.spread-chart />

    {{-- Most active + Biggest upsets + Most dominant --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <livewire:dashboard.most-active />
        <livewire:dashboard.biggest-upsets />
        <livewire:dashboard.most-dominant />
    </div>

    {{-- Top rivalries --}}
    <livewire:dashboard.top-rivalries />

</div>