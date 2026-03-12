@use('Illuminate\Support\Str')
<div class="flex flex-col gap-6">
    {{-- Player header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <img
                src="{{ asset('images/country_flags/' . strtolower($this->player->country_code) . '.svg') }}"
                alt="{{ $this->player->country }}"
                class="w-10 h-7 rounded-sm"
                title="{{ $this->player->country }}"
            >
            <div>
                <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->player->name }}</h1>
                <p class="text-zinc-500 dark:text-zinc-400">{{ $this->player->race }} · {{ $this->player->country }}</p>
            </div>
        </div>
        <flux:button
            variant="primary"
            icon="identification"
            x-on:click="$flux.modal('player-card').show()"
        >
            Player Card
        </flux:button>
    </div>

    {{-- Stats --}}
    @if($this->rating)
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Rating</p>
            <p class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->rating->rating }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Peak</p>
            <p class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->stats['peak_rating'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Games</p>
            <p class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->rating->games_played }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Win%</p>
            <p class="text-2xl font-bold {{ $this->stats['win_ratio'] >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $this->stats['win_ratio'] }}%</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Best streak</p>
            <p class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $this->stats['longest_win_streak'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Current streak</p>
            <p class="text-2xl font-bold {{ $this->stats['current_streak'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $this->stats['current_streak'] > 0 ? '+' : '' }}{{ $this->stats['current_streak'] }}
            </p>
        </div>
    </div>

    {{-- Race stats --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach($this->raceStats as $stat)
        @php
            $raceColor = match($stat['race']) {
                'Terran'  => 'border-blue-500 dark:border-blue-400',
                'Zerg'    => 'border-purple-500 dark:border-purple-400',
                'Protoss' => 'border-yellow-500 dark:border-yellow-400',
                default   => 'border-zinc-200 dark:border-zinc-700',
            };
            $raceText = match($stat['race']) {
                'Terran'  => 'text-blue-500 dark:text-blue-400',
                'Zerg'    => 'text-purple-500 dark:text-purple-400',
                'Protoss' => 'text-yellow-500 dark:text-yellow-400',
                default   => 'text-zinc-500 dark:text-zinc-400',
            };
        @endphp
        <div class="rounded-xl border-2 {{ $raceColor }} p-4">
            <p class="text-sm font-medium {{ $raceText }}">vs {{ $stat['race'] }}</p>
            <p class="text-2xl font-bold {{ $stat['ratio'] >= 50 ? 'text-green-500' : 'text-red-500' }}">{{ $stat['ratio'] }}%</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['wins'] }}W / {{ $stat['losses'] }}L</p>
        </div>
        @endforeach
    </div>

    {{-- Head to Head --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Head to Head</p>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Opponent</flux:table.column>
                <flux:table.column>W</flux:table.column>
                <flux:table.column>L</flux:table.column>
                <flux:table.column>Games</flux:table.column>
                <flux:table.column>Win%</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->headToHead as $h2h)
                <flux:table.row :key="$h2h['opponent']->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <img
                                src="{{ asset('images/country_flags/' . strtolower($h2h['opponent']->country_code) . '.svg') }}"
                                alt="{{ $h2h['opponent']->country }}"
                                class="w-6 h-4 rounded-sm"
                                title="{{ $h2h['opponent']->country }}"
                            >
                            <a href="{{ route('players.show', ['id' => $h2h['opponent']->id, 'slug' => Str::slug($h2h['opponent']->name)]) }}"
                               class="hover:underline">
                                {{ $h2h['opponent']->name }}
                            </a>
                            @php
                                $raceText = match($h2h['opponent']->race) {
                                    'Terran'  => 'text-blue-500 dark:text-blue-400',
                                    'Zerg'    => 'text-purple-500 dark:text-purple-400',
                                    'Protoss' => 'text-yellow-500 dark:text-yellow-400',
                                    default   => 'text-zinc-400',
                                };
                            @endphp
                            <span class="text-xs {{ $raceText }}">{{ $h2h['opponent']->race }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-green-500">{{ $h2h['wins'] }}</flux:table.cell>
                    <flux:table.cell class="text-red-500">{{ $h2h['losses'] }}</flux:table.cell>
                    <flux:table.cell>{{ $h2h['total'] }}</flux:table.cell>
                    <flux:table.cell>
                        <span class="{{ $h2h['ratio'] >= 50 ? 'text-green-500' : 'text-red-500' }}">
                            {{ $h2h['ratio'] }}%
                        </span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    @else
    {{-- No rating yet --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 text-center">
        <p class="text-zinc-400 dark:text-zinc-500">No rating data yet — this player has no recalculated games.</p>
    </div>
    @endif

    {{-- ELO Chart --}}
    @if($this->history->isNotEmpty())
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">ELO History</p>
        <div
            x-data="{
                init() {
                    new ApexCharts(this.$refs.chart, {
                        chart: {
                            type: 'line',
                            height: 300,
                            toolbar: { show: false },
                            background: 'transparent',
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        },
                        series: [{
                            name: 'Rating',
                            data: @js($this->history->pluck('rating_after'))
                        }],
                        xaxis: {
                            categories: @js($this->history->pluck('played_at')),
                            labels: { show: false },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2,
                        },
                        tooltip: {
                            x: {
                                formatter: (val, { dataPointIndex }) => {
                                    const dates = @js($this->history->pluck('played_at'));
                                    return dates[dataPointIndex];
                                }
                            }
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

    {{-- Game history --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">Game History</p>
        <livewire:players.game-history :playerId="$this->player->id" />
    </div>

    {{-- Player Card Modal --}}
    <flux:modal name="player-card" class="w-96">
        <div class="relative overflow-hidden rounded-2xl bg-zinc-900 text-white p-6 flex flex-col gap-4"
             style="background: linear-gradient(135deg, #18181b 60%, #27272a 100%);">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/country_flags/' . strtolower($this->player->country_code) . '.svg') }}"
                    class="w-8 h-6 rounded-sm"
                >
                <div>
                    <p class="text-xl font-bold">{{ $this->player->name }}</p>
                    <p class="text-sm text-zinc-400">{{ $this->player->race }} · {{ $this->player->country }}</p>
                </div>
            </div>
            <hr class="border-zinc-700">
            @if($this->rating)
            {{-- Stats grid --}}
            <div class="grid grid-cols-4 gap-3 text-center">
                <div>
                    <p class="text-2xl font-bold text-yellow-400">#{{ $this->stats['best_rank'] }}</p>
                    <p class="text-xs text-zinc-400">Best rank</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $this->rating->rating }}</p>
                    <p class="text-xs text-zinc-400">Rating</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $this->stats['peak_rating'] }}</p>
                    <p class="text-xs text-zinc-400">Peak</p>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $this->stats['win_ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $this->stats['win_ratio'] }}%
                    </p>
                    <p class="text-xs text-zinc-400">Win%</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-400">{{ $this->rating->wins }}</p>
                    <p class="text-xs text-zinc-400">Wins</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-400">{{ $this->rating->losses }}</p>
                    <p class="text-xs text-zinc-400">Losses</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">{{ $this->rating->games_played }}</p>
                    <p class="text-xs text-zinc-400">Games</p>
                </div>
                <div>
                    <p class="text-2xl font-bold {{ $this->stats['current_streak'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $this->stats['current_streak'] > 0 ? '+' : '' }}{{ $this->stats['current_streak'] }}
                    </p>
                    <p class="text-xs text-zinc-400">Streak</p>
                </div>
            </div>
            <hr class="border-zinc-700">
            {{-- Race stats --}}
            <div class="grid grid-cols-3 gap-2 text-center">
                @foreach($this->raceStats as $stat)
                @php
                    $raceColor = match($stat['race']) {
                        'Terran'  => 'text-blue-400',
                        'Zerg'    => 'text-purple-400',
                        'Protoss' => 'text-yellow-400',
                        default   => 'text-zinc-400',
                    };
                @endphp
                <div>
                    <p class="text-xs {{ $raceColor }} font-medium">vs {{ $stat['race'] }}</p>
                    <p class="font-bold {{ $stat['ratio'] >= 50 ? 'text-green-400' : 'text-red-400' }}">{{ $stat['ratio'] }}%</p>
                    <p class="text-xs text-zinc-500">{{ $stat['wins'] }}W / {{ $stat['losses'] }}L</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-zinc-400 text-sm text-center">No rating data yet.</p>
            @endif
        </div>
    </flux:modal>
</div>