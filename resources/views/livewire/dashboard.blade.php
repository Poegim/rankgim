<div class="flex flex-col gap-4">

{{-- Upcoming Events --}}
@if($this->upcomingEvents->isNotEmpty())
<div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
    <div class="flex items-center justify-between px-4 pt-4 pb-3">
        <h2 class="text-xs font-medium uppercase tracking-widest text-zinc-500 dark:text-zinc-400">📅 Upcoming Events</h2>
        <a href="{{ route('events.index') }}" class="text-sm text-zinc-500 hover:text-zinc-200 transition-colors" wire:navigate>
            View all →
        </a>
    </div>

    <div class="flex flex-col gap-2 px-3 pb-3">
        @foreach($this->upcomingEvents as $event)
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 p-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">

            {{-- Left: name + description + links --}}
            <div class="flex flex-col gap-2 min-w-0 overflow-hidden">

                {{-- Badge + name --}}
                <div class="flex items-center gap-2">
                    @if($event->isStream())
                    <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-500/15 text-purple-300 border border-purple-500/25">
                        Stream
                    </span>
                    @else
                    <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-amber-500/15 text-amber-300 border border-amber-500/25">
                        Open
                    </span>
                    @endif
                    <p class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white truncate">{{ $event->name }}</p>
                </div>

                {{-- Description --}}
                @if($event->description)
                <p class="text-xs text-zinc-500 truncate">{{ $event->description }}</p>
                @endif

                {{-- Players --}}
                @if($event->players->isNotEmpty())
                <div class="flex flex-wrap gap-1.5">
                    @foreach($event->players as $p)
                    <a href="{{ route('players.show', ['id' => $p->id, 'slug' => $p->name]) }}" wire:navigate
                        class="mr-1.5 inline-flex items-center text-xs {{ $p->race === 'Terran' ? 'text-blue-400 hover:text-blue-300' : ($p->race === 'Zerg' ? 'text-purple-400 hover:text-purple-300' : 'text-yellow-400 hover:text-yellow-300') }}">
                        <img src="{{ asset('images/country_flags/' . strtolower($p->country_code) . '.svg') }}"
                            class="w-4 h-3 mr-0.5 rounded-sm shrink-0">
                        {{ $p->name }}
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Links: full-width buttons on mobile, small pills on desktop --}}
                @php $links = $event->parsedLinks(); @endphp
                @if($links || $event->isRegistrationOpen())
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-1.5">
                    @foreach($links as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                        class="flex sm:inline-flex items-center justify-center sm:justify-start px-2.5 py-2 sm:py-0.5 rounded text-xs font-medium transition-opacity hover:opacity-80"
                        style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}40">
                        {{ $link['label'] ?: ucfirst($link['type']) }}
                    </a>
                    @endforeach
                    @if($event->isRegistrationOpen())
                    <span class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1.5 px-2.5 py-2 sm:py-0.5 rounded text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/25">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                        Registration open
                    </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Right: countdown + date — inline on mobile, stacked right on desktop --}}
            <div class="flex flex-col items-end sm:items-start sm:shrink-0">
                <p class="uppercase text-xs sm:text-sm font-mono font-bold {{ $event->isStream() ? 'text-purple-300' : 'text-amber-300' }}">
                    <span x-data x-text="new Intl.DateTimeFormat(navigator.language, {
                        day: 'numeric',
                        month: 'long',
                        hour: '2-digit',
                        minute: '2-digit',
                        timeZone: 'Europe/Warsaw'
                    }).format(new Date({{ $event->starts_at->timestamp }} * 1000))"></span>
                    <span class="opacity-50 text-xs">CET</span>
                </p>
                <div class="text-xs sm:text-sm font-mono {{ $event->isStream() ? 'text-purple-300/60' : 'text-amber-300/60' }} tabular-nums"
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
                </div>
            </div>

        </div>
        @endforeach
    </div>
</div>
@endif
    {{-- Top players --}}
    <livewire:dashboard.top-players />

    {{-- Recent achievements --}}
    <livewire:recent-achievements />
</div>