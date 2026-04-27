<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-700/60">
        <div class="flex items-center gap-2">
            <span class="text-base">💤</span>
            <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
                Inactive Players Ranking
            </span>
        </div>
        <span class="text-xs text-zinc-500">
            {{ $this->totalCount }} players · inactive &gt; {{ config('rankgim.inactive_months') }} months
        </span>
    </div>

    @if($this->players->isEmpty())
        <div class="px-5 py-8 text-center text-zinc-500 text-sm">
            No inactive players found.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-zinc-700/40 text-xs text-zinc-500 uppercase tracking-wider">

                        {{-- Non-sortable rank column --}}
                        <th class="px-4 py-3 w-10">#</th>

                        {{-- Non-sortable player name column --}}
                        <th class="px-4 py-3">Player</th>

                        {{-- Sortable columns --}}
                        @foreach([
                            'rating'       => 'Rating',
                            'wins'         => 'W',
                            'losses'       => 'L',
                            'games_played' => 'Games',
                            'last_played'  => 'Last played',
                        ] as $col => $label)
                            <th class="px-4 py-3 text-right">
                                <button
                                    wire:click="sort('{{ $col }}')"
                                    class="inline-flex items-center gap-1 ml-auto transition-colors
                                        {{ $sortBy === $col ? 'text-amber-400' : 'text-zinc-500 hover:text-zinc-300' }}"
                                >
                                    {{ $label }}
                                    <span class="text-[10px] opacity-70">{{ $this->sortIcon($col) }}</span>
                                </button>
                            </th>
                        @endforeach

                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700/30">
                    @foreach($this->players as $row)
                        <tr class="hover:bg-zinc-700/20 transition-colors">

                            {{-- Rank --}}
                            <td class="px-4 py-2.5 text-zinc-500 tabular-nums text-xs">
                                {{ $row->rank }}
                            </td>

                            {{-- Player name + flag --}}
                            <td class="px-4 py-2.5">
                                <a
                                    href="{{ route('players.show', ['id' => $row->player_id, 'slug' => \Illuminate\Support\Str::slug($row->name)]) }}"
                                    class="flex items-center gap-2 text-zinc-200 hover:text-white transition-colors"
                                    target="_blank"
                                >
                                    @if($row->country_code)
                                        <img
                                            src="https://flagcdn.com/w40/{{ strtolower($row->country_code) }}.png"
                                            alt="{{ $row->country_code }}"
                                            class="w-5 h-3.5 rounded-sm object-cover shrink-0"
                                        >
                                    @endif
                                    <span>{{ $row->name }}</span>
                                </a>
                            </td>

                            {{-- Rating --}}
                            <td class="px-4 py-2.5 text-right tabular-nums font-mono
                                {{ $sortBy === 'rating' ? 'text-amber-300' : 'text-zinc-300' }}">
                                {{ number_format($row->rating) }}
                            </td>

                            {{-- Wins --}}
                            <td class="px-4 py-2.5 text-right tabular-nums text-xs
                                {{ $sortBy === 'wins' ? 'text-emerald-300' : 'text-emerald-400' }}">
                                {{ $row->wins }}
                            </td>

                            {{-- Losses --}}
                            <td class="px-4 py-2.5 text-right tabular-nums text-xs
                                {{ $sortBy === 'losses' ? 'text-red-300' : 'text-red-400' }}">
                                {{ $row->losses }}
                            </td>

                            {{-- Games played --}}
                            <td class="px-4 py-2.5 text-right tabular-nums
                                {{ $sortBy === 'games_played' ? 'text-zinc-200' : 'text-zinc-400' }}">
                                {{ $row->games_played }}
                            </td>

                            {{-- Last played --}}
                            <td class="px-4 py-2.5 text-right tabular-nums text-xs
                                {{ $sortBy === 'last_played' ? 'text-zinc-300' : 'text-zinc-500' }}">
                                {{ $row->last_played ? \Carbon\Carbon::parse($row->last_played)->format('d M Y') : '—' }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->totalPages > 1)
            <div class="flex items-center justify-between px-5 py-3 border-t border-zinc-700/40 text-xs text-zinc-500">

                <span>
                    Page {{ $this->getPage() }} of {{ $this->totalPages }}
                </span>

                <div class="flex items-center gap-1">

                    {{-- Previous --}}
                    @if($this->getPage() > 1)
                        <button
                            wire:click="previousPage"
                            class="px-2.5 py-1 rounded bg-zinc-700/50 hover:bg-zinc-600/60 text-zinc-300 transition-colors"
                        >
                            ← Prev
                        </button>
                    @endif

                    {{-- Page numbers — show up to 7 around current page --}}
                    @php
                        $current = $this->getPage();
                        $total   = $this->totalPages;
                        $window  = collect(range(max(1, $current - 3), min($total, $current + 3)));
                    @endphp

                    @if($window->first() > 1)
                        <button wire:click="gotoPage(1)" class="px-2.5 py-1 rounded hover:bg-zinc-700/50 transition-colors">1</button>
                        @if($window->first() > 2)
                            <span class="px-1 text-zinc-600">…</span>
                        @endif
                    @endif

                    @foreach($window as $p)
                        <button
                            wire:click="gotoPage({{ $p }})"
                            class="px-2.5 py-1 rounded transition-colors
                                {{ $p === $current
                                    ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                                    : 'hover:bg-zinc-700/50 text-zinc-400 hover:text-zinc-200' }}"
                        >
                            {{ $p }}
                        </button>
                    @endforeach

                    @if($window->last() < $total)
                        @if($window->last() < $total - 1)
                            <span class="px-1 text-zinc-600">…</span>
                        @endif
                        <button wire:click="gotoPage({{ $total }})" class="px-2.5 py-1 rounded hover:bg-zinc-700/50 transition-colors">{{ $total }}</button>
                    @endif

                    {{-- Next --}}
                    @if($this->getPage() < $this->totalPages)
                        <button
                            wire:click="nextPage"
                            class="px-2.5 py-1 rounded bg-zinc-700/50 hover:bg-zinc-600/60 text-zinc-300 transition-colors"
                        >
                            Next →
                        </button>
                    @endif

                </div>
            </div>
        @endif

    @endif

</div>