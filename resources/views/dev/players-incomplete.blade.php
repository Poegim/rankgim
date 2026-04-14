<x-layouts::app>
    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-xs font-mono text-zinc-500 uppercase tracking-widest mb-1">dev tool</p>
            <h1 class="text-2xl font-semibold text-zinc-100">Players needing data</h1>
            <p class="text-sm text-zinc-500 mt-1">Main players (not aliases) with missing race or country, sorted by games played.</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-3">
                <div class="font-mono text-xl font-semibold text-white">{{ $stats['total'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-0.5">total</div>
            </div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-3">
                <div class="font-mono text-xl font-semibold text-amber-400">{{ $stats['missing_race'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-0.5">missing race only</div>
            </div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-3">
                <div class="font-mono text-xl font-semibold text-red-400">{{ $stats['missing_country'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-0.5">missing country only</div>
            </div>
            <div class="bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-3">
                <div class="font-mono text-xl font-semibold text-indigo-400">{{ $stats['missing_both'] }}</div>
                <div class="text-xs text-zinc-500 uppercase tracking-wide mt-0.5">missing both</div>
            </div>
        </div>

        {{-- Search --}}
        <div class="mb-4">
            <input
                type="text"
                id="search"
                placeholder="Search by name, country…"
                autocomplete="off"
                class="w-full bg-zinc-900 border border-zinc-800 rounded-lg px-4 py-2.5 text-sm text-zinc-200 placeholder-zinc-600 focus:outline-none focus:border-zinc-600"
            >
        </div>

        {{-- Table --}}
        <div class="border border-zinc-800 rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-zinc-900 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider w-16">Games</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Country</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Race</th>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">Missing</th>
                    </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-zinc-800">
                    @foreach ($players as $player)
                    <tr class="hover:bg-zinc-900/60 transition-colors">
                        <td class="px-4 py-2.5 font-mono text-indigo-400 text-xs">{{ $player->games_count }}</td>
                        <td class="px-4 py-2.5 font-medium text-zinc-200">
                            <a href="{{ $player->profile_url }}" class="hover:text-indigo-400 transition-colors">
                                {{ $player->name }}
                            </a>
                        </td>
                        <td class="px-4 py-2.5 text-zinc-400 whitespace-nowrap">
                            @if ($player->country_code && $player->country_code !== 'XX')
                                <img src="https://flagcdn.com/16x12/{{ strtolower($player->country_code) }}.png"
                                     width="16" height="12"
                                     class="inline-block mr-1.5 opacity-75 align-middle"
                                     alt="{{ $player->country_code }}">
                            @endif
                            {{ $player->country ?? '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-zinc-400">{{ $player->race ?? '—' }}</td>
                        <td class="px-4 py-2.5 space-x-1">
                            @if ($player->missing_race)
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-400/10 text-amber-400 uppercase tracking-wide">no race</span>
                            @endif
                            @if ($player->missing_country)
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded bg-red-400/10 text-red-400 uppercase tracking-wide">no country</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-zinc-600 text-center mt-4">{{ $stats['total'] }} players · rankgim.eu</p>
    </div>

    <script>
        const input = document.getElementById('search');
        const rows  = document.querySelectorAll('#tbody tr');

        input.addEventListener('input', () => {
            const q = input.value.toLowerCase();
            rows.forEach(row => {
                row.classList.toggle('hidden', q.length > 0 && !row.textContent.toLowerCase().includes(q));
            });
        });
    </script>
</x-layouts::app>