<div class="w-full flex flex-col gap-6 px-4 sm:px-6 lg:px-8">
    {{-- ========== GÓRA: Events (lewo) + Ranking (prawo) ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEWA: Events --}}
        <div class="lg:col-span-6 flex flex-col min-h-0">
            @if($this->upcomingEvents->isNotEmpty())
                <div class="overflow-hidden flex flex-col flex-1 min-h-0">
                    <div class="flex items-center justify-between px-4 pb-3 shrink-0">
                        <h2 class="text-xs font-medium uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                            📅 Upcoming Events
                        </h2>
                        <a href="{{ route('events.index') }}"
                           class="text-sm text-zinc-500 hover:text-zinc-200 transition-colors"
                           wire:navigate>
                            View all →
                        </a>
                    </div>
                    <div x-data="{
                        canUp: false, canDown: false,
                        update() {
                            const el = this.$refs.list;
                            const isDesktop = window.innerWidth >= 1024;
                            this.canUp   = isDesktop && el.scrollTop > 4;
                            this.canDown = isDesktop && el.scrollTop + el.clientHeight < el.scrollHeight - 4;
                        },
                        up()   { this.$refs.list.scrollBy({ top: -240, behavior: 'smooth' }); },
                        down() { this.$refs.list.scrollBy({ top:  240, behavior: 'smooth' }); },
                    }"
                    x-init="$nextTick(() => update()); window.addEventListener('resize', () => update())"
                    class="relative min-h-0 flex-1">

                        {{-- Up arrow --}}
                        <button
                            x-on:click="up()"
                            x-show="canUp"
                            class="absolute rotate-90 top-0 left-1/2 -translate-x-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-zinc-900/80 border border-zinc-700 text-zinc-300 hover:text-white hover:border-zinc-500 transition-all cursor-pointer text-xl shadow-lg"
                            style="display: none;"
                        >‹</button>

                        {{-- Down arrow --}}
                        <button
                            x-on:click="down()"
                            x-show="canDown"
                            class="absolute rotate-90 bottom-0 left-1/2 -translate-x-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-zinc-900/80 border border-zinc-700 text-zinc-300 hover:text-white hover:border-zinc-500 transition-all cursor-pointer text-xl shadow-lg"
                            style="display: none;"
                        >›</button>

                        {{-- Scrollable list --}}
                        <div x-ref="list"
                             x-on:scroll="update()"
                             class="flex flex-col gap-3 px-3 pb-3 overflow-y-auto h-full lg:max-h-[450px]"
                             style="scrollbar-width: none; -ms-overflow-style: none;">
                            @foreach($this->upcomingEvents as $event)
                                <x-event-card :event="$event" />
                            @endforeach
                        </div>

                    </div>
                </div>
            @endif
        </div>

        {{-- PRAWA: Ranking --}}
        <aside class="lg:col-span-6">
            <livewire:dashboard.top-players />
        </aside>
    </div>

    {{-- ========== DÓŁ: Achievements na pełnej szerokości ========== --}}
    <livewire:recent-achievements />

    {{-- ========== Last reactions + Last comments ========== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <livewire:dashboard.recent-reactions />
        <livewire:dashboard.recent-comments />
    </div>

</div>