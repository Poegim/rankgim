<x-layouts::app>
    <div class="max-w-3xl mx-auto px-4 py-12 flex flex-col gap-8">

        {{-- Hero --}}
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-zinc-600 dark:text-zinc-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                    </svg>
                </div>
                <span class="text-xs font-mono text-zinc-400 uppercase tracking-widest">About</span>
            </div>
            <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Rankgim</h1>
            <p class="text-lg text-zinc-500 dark:text-zinc-400 leading-relaxed">
                A community-driven ELO ranking system for <span class="text-zinc-800 dark:text-zinc-200 font-medium">StarCraft: Brood War</span> — one of the most competitive real-time strategy games ever made.
            </p>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-6">
            <div class="flex flex-col gap-1">
                <span class="text-2xl font-bold text-zinc-800 dark:text-white">{{ number_format($totalGames) }}</span>
                <span class="text-sm text-zinc-500">Games in the database</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-2xl font-bold text-zinc-800 dark:text-white">{{ $totalPlayers }}</span>
                <span class="text-sm text-zinc-500">Rated players</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-2xl font-bold text-zinc-800 dark:text-white">
                    {{ \Carbon\Carbon::parse($firstGame)->year }}–{{ \Carbon\Carbon::parse($lastGame)->year }}
                </span>
                <span class="text-sm text-zinc-500">Years covered</span>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- How ELO works --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-zinc-50 dark:bg-zinc-800/50">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">How ELO works</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-bold text-zinc-800 dark:text-white">1000</span>
                    <span class="text-sm text-zinc-500">Starting rating for every player</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-bold text-zinc-800 dark:text-white">K=40</span>
                    <span class="text-sm text-zinc-500">K-factor — how much each game matters</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-2xl font-bold text-zinc-800 dark:text-white">Global</span>
                    <span class="text-sm text-zinc-500">Single unified rating across all tournaments</span>
                </div>
            </div>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                Beating a higher-rated player earns more points than beating a lower-rated one. Upsets are rewarded, dominance is recognised.
            </p>
        </div>


        {{-- Credits --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Credits</h2>
            <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                Special thanks to <span class="font-medium text-zinc-800 dark:text-white">GruGloG</span> and <span class="font-medium text-zinc-800 dark:text-white">Ziggy</span> for submitting game results to the database — 577 and 317 games added respectively.
            </p>
        </div>

        {{-- Author --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Author</h2>
            <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                Built by <span class="font-medium text-zinc-800 dark:text-white">poegim</span>. To get in touch, find me under the same nick on Netwars, TeamLiquid or Battle.net.
            </p>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- Tech stack --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Built with</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(['Laravel 12', 'Livewire', 'Flux UI', 'Alpine.js', 'Tailwind CSS', 'ApexCharts', 'MySQL'] as $tech)
                <span class="px-3 py-1 rounded-full border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-800">
                    {{ $tech }}
                </span>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts::app>