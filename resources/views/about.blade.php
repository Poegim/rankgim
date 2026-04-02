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
                An ELO ranking system for <span class="text-zinc-800 dark:text-zinc-200 font-medium">StarCraft: Remastered / Brood War</span> — tracking amateur tournament results and calculating global player ratings.
            </p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                Rankgim processes results from community tournaments, calculates ratings, and provides detailed statistics. The goal is to give the Brood War scene a reliable way to see how players and countries compare over time.
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

        {{-- How it works --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">How the ranking works</h2>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 flex flex-col gap-4 bg-zinc-50 dark:bg-zinc-800/50">
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
            </div>

            <div class="flex flex-col gap-3 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                <p>
                    Rankgim uses the <span class="font-medium text-zinc-800 dark:text-zinc-200">ELO rating system</span> — the same concept used in chess. Every player starts at 1000 and their rating goes up or down based on match results.
                </p>
                <p>
                    Beating a higher-rated player earns you more points than beating someone rated below you. Losing to a weaker opponent costs you more than losing to a stronger one. Upsets are rewarded, dominance is recognised.
                </p>
                <p>
                    The <span class="font-medium text-zinc-800 dark:text-zinc-200">K-factor of 40</span> determines how strongly each game affects a player's rating. To handle new high level players Rankgim adjusts K values:
                </p>
                <ul>
                    <li><span class="font-medium text-zinc-800 dark:text-zinc-200">New players (<15 games):</span> gain rating points quickly when they win, allowing them to reach their true skill level faster.</li>
                    <li><span class="font-medium text-zinc-800 dark:text-zinc-200">Opponents of these new players:</span> lose fewer points in these matches, preventing sudden drops in the ranking of experienced top players.</li>
                    <li><span class="font-medium text-zinc-800 dark:text-zinc-200">After 15 games:</span> K values normalize to the standard K-factor (40), ensuring stable and fair rating changes for all players.</li>
                </ul>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- Ranking rules --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Ranking rules</h2>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 bg-zinc-50 dark:bg-zinc-800/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎮</span>
                            <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white text-sm">Minimum 15 games </span>
                        </div>
                        <p class="text-sm text-zinc-500">Players need at least 15 rated games to appear in the ranking. This filters out one-time participants and ensures ratings are meaningful.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📅</span>
                            <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white text-sm">Active in last 12 months</span>
                        </div>
                        <p class="text-sm text-zinc-500">Only players who have played at least one game in the last 12 months (from the date of the most recent game) are shown in the ranking.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔄</span>
                            <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white text-sm">Full recalculation</span>
                        </div>
                        <p class="text-sm text-zinc-500">Ratings are recalculated from scratch every time new games are added. All games are processed in chronological order to ensure accuracy.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white text-sm">AKA system</span>
                        </div>
                        <p class="text-sm text-zinc-500">Players who use multiple nicknames are linked together. All their games count towards a single rating, so the ranking reflects the actual person, not the alias.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- Support --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Support</h2>
            <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                Rankgim is free and always will be. If you find it useful and want to help keep the servers running, you can buy me a coffee.
            </p>
            <a href="https://ko-fi.com/rankgim" target="_blank"
               class="group relative flex items-center justify-center gap-3 rounded-xl px-6 py-5 overflow-hidden transition-all duration-300 hover:scale-[1.01] hover:shadow-lg hover:shadow-pink-500/20"
               style="background: linear-gradient(135deg, #ff5e5b 0%, #ff2d55 40%, #d63384 100%);">
                <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-all duration-300"></div>
                <svg class="w-8 h-8 text-white shrink-0 relative z-10 group-hover:animate-bounce" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M23.881 8.948c-.773-4.085-4.859-4.593-4.859-4.593H.723c-.604 0-.679.798-.679.798s-.082 7.324-.022 11.822c.164 2.424 2.586 2.672 2.586 2.672s8.267-.023 11.966-.049c2.438-.426 2.683-2.566 2.658-3.734 4.352.24 7.422-2.831 6.649-6.916zm-11.062 3.511c-1.246 1.453-4.011 3.976-4.011 3.976s-.121.119-.31.023c-.076-.057-.108-.09-.108-.09-.443-.441-3.368-3.049-4.034-3.954-.709-.965-1.041-2.7-.091-3.71.951-1.01 3.005-1.086 4.363.407 0 0 1.565-1.782 3.468-.963 1.903.82 1.832 3.011.723 4.311zm6.173.478c-.928.116-1.682.028-1.682.028V7.284h1.77s1.971.551 1.971 2.638c0 1.913-.985 2.667-2.059 3.015z"/>
                </svg>
                <span class="text-white text-xl font-bold relative z-10">Help keep Rankgim alive ❤️</span>
            </a>
        </div>

        {{-- Divider --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700"></div>

        {{-- Credits --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Credits</h2>
            <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
                Special thanks to <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">GruGloG</span> and <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">Ziggy</span> for submitting game results to the database — 577 and 317 games added respectively.
            </p>
        </div>

        {{-- In honour --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">To the people who made those years unforgettable</h2>
            <div class="rounded-xl border border-zinc-300 dark:border-zinc-600 p-8 bg-zinc-100 dark:bg-zinc-800/80">
                <div class="flex flex-col items-center gap-6">
                    <p class="text-zinc-500 dark:text-zinc-400 text-center max-w-lg leading-relaxed">
                        For the hours we spent playing together, the conversations that lasted longer than the games, and all the emotions in between. Those were some of the best years of my life and I miss you guys.                    <div class="w-12 border-t border-zinc-300 dark:border-zinc-600"></div>
                    <div class="flex flex-wrap justify-center gap-x-6 gap-y-3">
                        @foreach(['moagim', 'MofD)Respect', 'PzH.MT', 'Azoun', 'Darkelf-', 'Katerina', 'Driver[soasc]', 'fallen.apollyon', 'ALK_aida', 'Effka[pG]', 'Sadef', 'GreenGosu', 'aFF]Kiv[', 'aFF]WolF[', 'aFF]sluslu[', 'Kebes', 'Apogeum', 'RedEyes', 'FuriaKutasów', 'peR aka eRa', 'metody[watb]'] as $friend)
                        <span class="text-lg font-bold tracking-wide bg-gradient-to-r from-amber-600 via-orange-500 to-red-400 dark:from-amber-400 dark:via-orange-400 dark:to-red-300 bg-clip-text text-transparent">
                            {{ $friend }}
                        </span>
                        @endforeach
                    </div>
                    <p class="text italic text-zinc-400 dark:text-zinc-200 text-center">
                        …and the rest of <b>StarCraft POL-1 @ Europe </b> — plus everyone I forgot, because I'm old and bad with names.
                        If you see this and feel you should be here, don't hesitate to reach out.
                    </p>
                </div>
            </div>
        </div>

{{-- Author --}}
<div class="flex flex-col gap-3">
    <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-widest">Author</h2>
    <div class="flex flex-col gap-2">
        <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
            Built by <span class="font-semibold text-zinc-800 dark:text-white">poegim</span> — also known as
        </p>
        <p class="text-sm font-mono text-zinc-500 dark:text-zinc-400 leading-relaxed">
            Krakow · aFF]ZuluNation · PzM.Zimbabwe · Poezja[T4] · iloveania · zulu[WL]Nation
        </p>
        <p class="text-zinc-600 dark:text-zinc-300 leading-relaxed">
            To get in touch, find me as <span class="font-semibold text-zinc-800 dark:text-white">poegim</span> on Netwars, TeamLiquid or Battle.net.
        </p>
    </div>
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