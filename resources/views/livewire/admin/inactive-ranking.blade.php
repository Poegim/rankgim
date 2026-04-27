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
            {{ $this->players->count() }} players · inactive &gt; {{ config('rankgim.inactive_months') }} months
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
                        <th class="px-4 py-3 w-10">#</th>
                        <th class="px-4 py-3">Player</th>
                        <th class="px-4 py-3 text-right">Rating</th>
                        <th class="px-4 py-3 text-right">W / L</th>
                        <th class="px-4 py-3 text-right">Games</th>
                        <th class="px-4 py-3 text-right">Last played</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700/30">
                    @foreach($this->players as $row)
                        <tr class="hover:bg-zinc-700/20 transition-colors">

                            {{-- Rank --}}
                            <td class="px-4 py-2.5 text-zinc-500 tabular-nums text-xs">
                                {{ $row->rank }}
                            </td>

                            {{-- Player name + flag + race --}}
                            <td class="px-4 py-2.5">
                                @if($row->player)
                                    <a
                                        href="{{ route('players.show', ['id' => $row->player->id, 'slug' => \Illuminate\Support\Str::slug($row->player->name)]) }}"
                                        class="flex items-center gap-2 text-zinc-200 hover:text-white transition-colors"
                                        target="_blank"
                                    >
                                        @if($row->player->country_code)
                                            <img
                                                src="https://flagcdn.com/w40/{{ strtolower($row->player->country_code) }}.png"
                                                alt="{{ $row->player->country_code }}"
                                                class="w-5 h-3.5 rounded-sm object-cover shrink-0"
                                            >
                                        @endif
                                        <span>{{ $row->player->name }}</span>
                                    </a>
                                @else
                                    <span class="text-zinc-500 italic">Unknown</span>
                                @endif
                            </td>

                            {{-- Rating --}}
                            <td class="px-4 py-2.5 text-right tabular-nums font-mono text-amber-300">
                                {{ number_format($row->rating) }}
                            </td>

                            {{-- W / L --}}
                            <td class="px-4 py-2.5 text-right tabular-nums text-xs">
                                <span class="text-emerald-400">{{ $row->wins }}</span>
                                <span class="text-zinc-600 mx-0.5">/</span>
                                <span class="text-red-400">{{ $row->losses }}</span>
                            </td>

                            {{-- Games played --}}
                            <td class="px-4 py-2.5 text-right tabular-nums text-zinc-400">
                                {{ $row->games_played }}
                            </td>

                            {{-- Last played --}}
                            <td class="px-4 py-2.5 text-right text-zinc-500 text-xs tabular-nums">
                                {{ $row->last_played ? \Carbon\Carbon::parse($row->last_played)->format('d M Y') : '—' }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>