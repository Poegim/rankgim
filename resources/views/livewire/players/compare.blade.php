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
                   class="text-xl font-bold hover:underline text-travertine-900 dark:text-white">
                    {{ $this->player1->name }}
                </a>
                <p class="text-sm text-travertine-500 dark:text-zinc-500">
                    {{ $this->player1->race }} · {{ $this->player1->country }}
                </p>
                @if($this->rating1)
                    <p class="text-2xl font-bold mt-1 text-travertine-900 dark:text-white">
                        {{ $this->rating1->rating }}
                    </p>
                @endif
            </div>
        </div>

        {{-- VS --}}
        <div class="text-center">
            <span class="text-3xl font-black text-travertine-300 dark:text-zinc-600">VS</span>
        </div>

        {{-- Player 2 --}}
        <div class="flex items-center gap-3 justify-end text-right">
            <div>
                <a href="{{ route('players.show', ['id' => $this->player2->id, 'slug' => Str::slug($this->player2->name)]) }}"
                   class="text-xl font-bold hover:underline text-travertine-900 dark:text-white">
                    {{ $this->player2->name }}
                </a>
                <p class="text-sm text-travertine-500 dark:text-zinc-500">
                    {{ $this->player2->race }} · {{ $this->player2->country }}
                </p>
                @if($this->rating2)
                    <p class="text-2xl font-bold mt-1 text-travertine-900 dark:text-white">
                        {{ $this->rating2->rating }}
                    </p>
                @endif
            </div>
            <img src="{{ asset('images/country_flags/' . strtolower($this->player2->country_code) . '.svg') }}"
                 class="w-10 h-7 rounded-sm shrink-0">
        </div>

    </div>

    {{-- H2H Stats --}}
    @if($this->h2h['total'] > 0)
    <div class="rounded-xl border p-6
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <p class="text-sm font-medium text-center mb-4 text-travertine-500 dark:text-zinc-400">
            ⚔️ Head to Head — {{ $this->h2h['total'] }} games
        </p>

        <div class="grid grid-cols-3 items-center gap-4 mb-4">
            <div class="text-center">
                <p class="text-4xl font-black
                    {{ $this->h2h['p1wins'] > $this->h2h['p2wins'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-travertine-900 dark:text-white' }}">
                    {{ $this->h2h['p1wins'] }}
                </p>
                <p class="text-sm text-travertine-500 dark:text-zinc-500">wins</p>
            </div>
            <div class="text-center">
                <p class="text-sm text-travertine-400 dark:text-zinc-400">
                    {{ $this->h2h['p1ratio'] }}% — {{ $this->h2h['p2ratio'] }}%
                </p>
            </div>
            <div class="text-center">
                <p class="text-4xl font-black
                    {{ $this->h2h['p2wins'] > $this->h2h['p1wins'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-travertine-900 dark:text-white' }}">
                    {{ $this->h2h['p2wins'] }}
                </p>
                <p class="text-sm text-travertine-500 dark:text-zinc-500">wins</p>
            </div>
        </div>

        {{-- Progress bar — Polymarket convention: A=blue, B=red (rule #5, brand/semantic) --}}
        <div class="h-3 rounded-full overflow-hidden flex
            bg-travertine-200 dark:bg-zinc-700">
            <div class="h-full bg-blue-500 transition-all"
                 style="width: {{ $this->h2h['p1ratio'] }}%"></div>
            <div class="h-full bg-red-500 flex-1"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-xs font-medium text-blue-600 dark:text-blue-500">{{ $this->player1->name }}</span>
            <span class="text-xs font-medium text-red-600 dark:text-red-500">{{ $this->player2->name }}</span>
        </div>
    </div>
    @else
    <div class="rounded-xl border p-6 text-center
        border-travertine-300 dark:border-zinc-700">
        <p class="text-travertine-400 dark:text-zinc-400">These players have never faced each other.</p>
    </div>
    @endif

    {{-- Rating history chart --}}
    @if($this->ratingHistory1->isNotEmpty() || $this->ratingHistory2->isNotEmpty())
    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
            📈 Rating history
        </p>
        <div x-data="{
                init() {
                    const isDark    = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#71717a' : '#78716c';
                    const gridColor = isDark ? '#3f3f46' : '#d4cab0';

                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            toolbar: { show: false },
                            background: 'transparent',
                            // Disable scroll-to-zoom so page scroll works normally
                            zoom: { enabled: false },
                        },
                        theme: {
                            mode: isDark ? 'dark' : 'light',
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
                        // Blue/red — Polymarket convention, semantic brand colors (rule #5)
                        colors: ['#3b82f6', '#ef4444'],
                        xaxis: {
                            type: 'datetime',
                            labels: {
                                format: 'MMM yyyy',
                                style: { colors: textColor },
                            },
                        },
                        yaxis: {
                            labels: { style: { colors: textColor } },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                        },
                        legend: {
                            position: 'top',
                            labels: { colors: textColor },
                        },
                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4,
                        },
                        tooltip: {
                            theme: isDark ? 'dark' : 'light',
                        },
                    }).render();
                }
            }">
            <div x-ref="chart"></div>
        </div>
    </div>
    @endif

    {{-- Recent H2H games --}}
    @if($this->recentH2hGames->isNotEmpty())
    <div class="rounded-xl border p-4
        border-travertine-300 bg-travertine-50
        dark:border-zinc-700 dark:bg-transparent">
        <p class="font-cinzel text-[10px] font-medium uppercase tracking-[0.15em] text-oxblood dark:text-zinc-500 mb-4">
            🎮 Recent games
        </p>
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
                        <span class="text-xs text-travertine-400 dark:text-zinc-400">
                            {{ \Carbon\Carbon::parse($entry->played_at)->format('Y-m-d') }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="{{ route('tournaments.show', $entry->game->tournament_id) }}"
                           class="text-sm hover:underline text-travertine-500 dark:text-zinc-500">
                            {{ $entry->game->tournament->name ?? '—' }}
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{-- Blue = player1, red = player2, Polymarket convention (rule #5) --}}
                        <span class="font-medium
                            {{ $entry->game->winner->id === $this->id1
                                ? 'text-blue-600 dark:text-blue-500'
                                : 'text-red-600 dark:text-red-500' }}">
                            {{ $entry->game->winner->name }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-travertine-500 dark:text-zinc-400">
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