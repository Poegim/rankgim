@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">

    {{-- Player headers --}}
    <div class="grid grid-cols-3 gap-4 items-center">

        {{-- Player 1 --}}
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/country_flags/' . strtolower($this->player1->country_code) . '.svg') }}"
                 class="w-10 h-7 rounded-sm shrink-0">
            <div>
                <a href="{{ route('players.show', ['id' => $this->player1->id, 'slug' => Str::slug($this->player1->name)]) }}"
                   class="text-xl font-bold text-zinc-800 dark:text-white hover:underline">
                    {{ $this->player1->name }}
                </a>
                <p class="text-sm text-zinc-500">
                    {{ $this->player1->race }} · {{ $this->player1->country }}
                </p>
                @if($this->rating1)
                    <p class="text-2xl font-bold text-zinc-800 dark:text-white mt-1">{{ $this->rating1->rating }}</p>
                @endif
            </div>
        </div>

        {{-- VS --}}
        <div class="text-center">
            <span class="text-3xl font-black text-zinc-300 dark:text-zinc-600">VS</span>
        </div>

        {{-- Player 2 --}}
        <div class="flex items-center gap-3 justify-end text-right">
            <div>
                <a href="{{ route('players.show', ['id' => $this->player2->id, 'slug' => Str::slug($this->player2->name)]) }}"
                   class="text-xl font-bold text-zinc-800 dark:text-white hover:underline">
                    {{ $this->player2->name }}
                </a>
                <p class="text-sm text-zinc-500">
                    {{ $this->player2->race }} · {{ $this->player2->country }}
                </p>
                @if($this->rating2)
                    <p class="text-2xl font-bold text-zinc-800 dark:text-white mt-1">{{ $this->rating2->rating }}</p>
                @endif
            </div>
            <img src="{{ asset('images/country_flags/' . strtolower($this->player2->country_code) . '.svg') }}"
                 class="w-10 h-7 rounded-sm shrink-0">
        </div>

    </div>

    {{-- H2H Stats --}}
    @if($this->h2h['total'] > 0)
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4 text-center">⚔️ Head to Head — {{ $this->h2h['total'] }} games</p>

        <div class="grid grid-cols-3 items-center gap-4 mb-4">
            <div class="text-center">
                <p class="text-4xl font-black {{ $this->h2h['p1wins'] > $this->h2h['p2wins'] ? 'text-green-500' : 'text-zinc-800 dark:text-white' }}">
                    {{ $this->h2h['p1wins'] }}
                </p>
                <p class="text-sm text-zinc-500">wins</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-zinc-400">{{ $this->h2h['p1ratio'] }}% — {{ $this->h2h['p2ratio'] }}%</p>
            </div>
            <div class="text-center">
                <p class="text-4xl font-black {{ $this->h2h['p2wins'] > $this->h2h['p1wins'] ? 'text-green-500' : 'text-zinc-800 dark:text-white' }}">
                    {{ $this->h2h['p2wins'] }}
                </p>
                <p class="text-sm text-zinc-500">wins</p>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="h-3 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden flex">
            <div class="h-full bg-blue-500 transition-all"
                 style="width: {{ $this->h2h['p1ratio'] }}%"></div>
            <div class="h-full bg-red-500 flex-1"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-xs text-blue-500 font-medium">{{ $this->player1->name }}</span>
            <span class="text-xs text-red-500 font-medium">{{ $this->player2->name }}</span>
        </div>
    </div>
    @else
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 text-center">
        <p class="text-zinc-400">These players have never faced each other.</p>
    </div>
    @endif

    {{-- Rating chart --}}
    @if($this->ratingHistory1->isNotEmpty() || $this->ratingHistory2->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">📈 Rating history</p>
        <div
            x-data="{
                init() {
                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            toolbar: { show: false },
                            background: 'transparent',
                            zoom: {
        enabled: false  // disables scroll-to-zoom, restores page scroll
    },
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        },
                        series: [
                            {
                                name: '{{ $this->player1->name }}',
                                data: @js($this->ratingHistory1)
                            },
                            {
                                name: '{{ $this->player2->name }}',
                                data: @js($this->ratingHistory2)
                            }
                        ],
                        colors: ['#3b82f6', '#ef4444'],
                        xaxis: {
                            type: 'datetime',
                            labels: {
                                format: 'MMM yyyy',
                            },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                        },
                        legend: {
                            position: 'top',
                        },
                        grid: {
                            borderColor: document.documentElement.classList.contains('dark') ? '#3f3f46' : '#e4e4e7',
                        },
                    }).render();
                }
            }"
        >
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- Recent H2H games --}}
    @if($this->recentH2hGames->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-4">🎮 Recent games</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Date</flux:table.column>
                <flux:table.column>Tournament</flux:table.column>
                <flux:table.column>Winner</flux:table.column>
                <flux:table.column>Loser</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->recentH2hGames as $entry)
                <flux:table.row :key="$entry->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        <span class="text-xs text-zinc-400">
                            {{ \Carbon\Carbon::parse($entry->played_at)->format('Y-m-d') }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="{{ route('tournaments.show', $entry->game->tournament_id) }}"
                           class="text-sm text-zinc-500 hover:underline">
                            {{ $entry->game->tournament->name ?? '—' }}
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-medium {{ $entry->game->winner->id === $this->id1 ? 'text-blue-500' : 'text-red-500' }}">
                            {{ $entry->game->winner->name }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-zinc-500 dark:text-zinc-400">
                            {{ $entry->game->loser->name }}
                        </span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @endif

</div>