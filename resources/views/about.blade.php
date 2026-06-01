<x-layouts::app>
    <div class="max-w-3xl mx-auto px-4 py-12 flex flex-col gap-8">

        <div class="flex flex-col gap-4">
            <h1>
                <x-app-logo class="inline-flex w-auto h-10 mr-2" />
            </h1>

            <p class="text-lg leading-relaxed text-travertine-600 dark:text-zinc-300">
                Rankgim is an ELO ranking system for StarCraft: Remastered / Brood War, focused on tracking amateur
                tournament results and seeing how players stack up over time.
            </p>

            <p class="text-sm italic leading-relaxed text-travertine-500 dark:text-zinc-400">
                I originally built this for myself, but at some point it just felt right to share it.
            </p>

            <p class="text-sm leading-relaxed text-travertine-600 dark:text-zinc-300">
                It collects results from community tournaments, calculates ratings, and lets you explore stats, player
                histories, and matchups — all in one place.
            </p>
        </div>

        {{-- Divider --}}
        <div class="border-t border-travertine-300 dark:border-zinc-700"></div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-6">
            <div class="flex flex-col gap-1">
                <span class="font-mono text-3xl font-bold text-travertine-900 dark:text-white">
                    {{ number_format($totalGames) }}
                </span>
                <span class="text-sm text-travertine-500 dark:text-zinc-400">Games in the database</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="font-mono text-3xl font-bold text-travertine-900 dark:text-white">
                    {{ $totalPlayers }}
                </span>
                <span class="text-sm text-travertine-500 dark:text-zinc-400">Rated players</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="font-mono text-3xl font-bold text-travertine-900 dark:text-white">
                    {{ \Carbon\Carbon::parse($firstGame)->year }}–{{ \Carbon\Carbon::parse($lastGame)->year }}
                </span>
                <span class="text-sm text-travertine-500 dark:text-zinc-400">Years covered</span>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-travertine-300 dark:border-zinc-700"></div>

        {{-- How it works --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">How the ranking works</h2>

            <div class="rounded-xl p-6 flex flex-col gap-4
                bg-travertine-100 dark:bg-zinc-800/50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <span class="font-mono text-2xl font-bold text-travertine-900 dark:text-white">1000</span>
                        <span class="text-sm text-travertine-500 dark:text-zinc-400">Starting rating for every player</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-mono text-2xl font-bold text-travertine-900 dark:text-white">K=40</span>
                        <span class="text-sm text-travertine-500 dark:text-zinc-400">K-factor — how much each game matters</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-2xl font-bold text-travertine-900 dark:text-white">Global</span>
                        <span class="text-sm text-travertine-500 dark:text-zinc-400">Single unified rating across all tournaments</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 text-sm leading-relaxed text-travertine-600 dark:text-zinc-300">
                <p>
                    Rankgim uses the <span class="font-medium text-travertine-900 dark:text-white">ELO rating system</span>
                    — the same concept used in chess. Every player starts at 1000 and their rating goes up or down based
                    on match results.
                </p>
                <p>
                    Beating a higher-rated player earns you more points than beating someone rated below you. Losing to
                    a weaker opponent costs you more than losing to a stronger one. Upsets are rewarded, dominance is
                    recognised.
                </p>
            </div>

            {{-- Shield system --}}
            <div class="rounded-xl border p-5 flex flex-col gap-3
                border-travertine-300 bg-travertine-100 dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛡️</span>
                    <span class="font-semibold text-sm text-travertine-900 dark:text-white">Shield System</span>
                </div>
                <p class="text-sm leading-relaxed text-travertine-500 dark:text-zinc-300">
                    K-factor adjusts based on player experience:
                </p>
                <ul class="text-sm leading-relaxed list-none flex flex-col gap-2 text-travertine-500 dark:text-zinc-300">
                    <li>
                        <span class="font-medium text-travertine-900 dark:text-white">New players (&lt;15 games)</span> —
                        gain and lose rating at an accelerated rate (K=60), so they can reach their true skill level quickly.
                    </li>
                    <li>
                        <span class="font-medium text-travertine-900 dark:text-white">Veterans vs new players</span> — when
                        an established player faces someone with fewer than 15 games, the veteran's K-factor is reduced
                        to 20. This protects experienced players from big rating swings caused by unplaced opponents.
                    </li>
                    <li>
                        <span class="font-medium text-travertine-900 dark:text-white">After 15 games</span> — K-factor
                        normalises to the standard value of 40 for both sides.
                    </li>
                </ul>
            </div>
        </div>

        {{-- Scene scope --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Scene scope</h2>

            <div class="border-l-2 border-emerald-500/40 pl-5 flex flex-col gap-5">
                <p class="text-sm leading-relaxed text-travertine-700 dark:text-zinc-300">
                    Rankgim tracks the <span class="font-medium text-emerald-700 dark:text-emerald-300">foreigner scene</span> — the Brood War
                    community outside of
                    <span class="whitespace-nowrap"><img src="{{ asset('images/country_flags/kr.svg') }}"
                        class="inline w-5 h-3.5 rounded-sm mx-1 align-text-bottom">Korea</span> and
                    <span class="whitespace-nowrap"><img src="{{ asset('images/country_flags/cn.svg') }}"
                        class="inline w-5 h-3.5 rounded-sm mx-1 align-text-bottom">China.</span>
                    Those scenes are largely separated from the rest of the world, so the ranking only counts events
                    that are open to everyone.
                    <span class="text-travertine-900 dark:text-white">As long as the door is open, the result counts.</span>
                </p>

                {{-- What counts --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">✅</span>
                        <span class="font-semibold text-sm text-travertine-900 dark:text-white">What counts</span>
                    </div>
                    <ul class="text-sm leading-relaxed flex flex-col gap-1.5 pl-1 text-travertine-700 dark:text-zinc-300">
                        <li>— <span class="font-medium text-emerald-700 dark:text-emerald-300">Foreigner-scene tournaments with open qualifiers</span> — anyone can sign up, and a Korean or Chinese win counts like any other. A foreigner playing in a Korean or Chinese event is the opposite case — those results stay outside the ranking</li>
                        <li>— <span class="font-medium text-travertine-900 dark:text-white">Tiered events</span> (e.g. <span class="italic">Pro / Gosu / Main</span> divisions) — open entry, divisions assigned by skill</li>
                        <li>— <span class="font-medium text-travertine-900 dark:text-white">Show matches and invitationals between foreigners</span> — these are just matches between players already in the ranking, so the results belong here</li>
                        <li>— <span class="font-medium text-travertine-900 dark:text-white">Regional tournaments</span> (national championships, country leagues, etc.) — counted too, as long as those players also compete outside their own league. A closed pool playing only each other for years would drift from the rest of the ranking, and may be excluded — retroactively, if needed.</li>
                    </ul>
                </div>

                {{-- What doesn't --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🚫</span>
                        <span class="font-semibold text-sm text-travertine-900 dark:text-white">What doesn't</span>
                    </div>
                    <ul class="text-sm leading-relaxed flex flex-col gap-1.5 pl-1 text-travertine-700 dark:text-zinc-300">
                        <li>— <span class="font-medium text-travertine-900 dark:text-white">Cross-scene matchups</span> — events built specifically to bring foreigners face-to-face with Korean or Chinese players (show matches, invitationals, exhibitions, e.g. <span class="italic">BSL Non-Korean Championship</span>, <span class="italic">Team-A</span> style events). The matchup is arranged, not earned through an open bracket, so the result isn't comparable to regular tournament play</li>
                        <li>— <span class="font-medium text-travertine-900 dark:text-white">Skill-capped tournaments</span> with an MMR ceiling (e.g. <span class="italic">max 1800 MMR</span>) — a closed bracket, even if closed from the top instead of the sides</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Ranking rules --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Ranking rules</h2>

            <div class="rounded-xl border p-6
                border-travertine-300 bg-travertine-100
                dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎮</span>
                            <span class="font-semibold text-sm text-travertine-900 dark:text-white">Minimum 15 games</span>
                        </div>
                        <p class="text-sm text-travertine-500 dark:text-zinc-400">Players need at least 15 rated games to appear in the ranking — this filters out one-time participants.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📅</span>
                            <span class="font-semibold text-sm text-travertine-900 dark:text-white">Active in last {{ config('rankgim.inactive_months') }} months</span>
                        </div>
                        <p class="text-sm text-travertine-500 dark:text-zinc-400">Only players who have played at least one game in the last {{ config('rankgim.inactive_months') }} months are shown in the ranking.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔄</span>
                            <span class="font-semibold text-sm text-travertine-900 dark:text-white">Full recalculation</span>
                        </div>
                        <p class="text-sm text-travertine-500 dark:text-zinc-400">Ratings are recalculated from scratch every time new games are added.</p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">👤</span>
                            <span class="font-semibold text-sm text-travertine-900 dark:text-white">AKA system</span>
                        </div>
                        <p class="text-sm text-travertine-500 dark:text-zinc-400">Multiple nicknames are linked into one profile — one player, one rating. When a new alias is found, old games get added to the right profile and ratings are recalculated — for both the player and all of their past opponents.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Features</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach([
                    ['🏆', 'Global Rankings',            'Live ELO rankings with filters by race, region and search.'],
                    ['👤', 'Player Profiles',            'Rating history charts, game log, head-to-head stats and more for every player.'],
                    ['⚔️', 'Player & Country Comparison','Compare any two players or countries — head-to-head record, rating trends and matchup stats.'],
                    ['⭐', 'Achievements',               'Dozens of achievements across tiers S through D — from rating milestones to win streaks, rivalry feats and secret unlocks.'],
                    ['📊', 'Stats & History',            'Race matchups, yearly activity, biggest upsets, hot streaks, top rivalries and historical ranking charts.'],
                    ['🗓️', 'Tournaments & Games',        'Browse all tracked tournaments and individual game results.'],
                ] as [$icon, $title, $desc])
                <div class="rounded-xl border p-4 flex flex-col gap-1
                    border-travertine-300 bg-travertine-75
                    dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $icon }}</span>
                        <span class="font-semibold text-sm text-travertine-900 dark:text-white">{{ $title }}</span>
                    </div>
                    <p class="text-sm text-travertine-500 dark:text-zinc-400">{{ $desc }}</p>
                </div>
                @endforeach
            </div>

            {{-- Events — highlighted (amber brand color, stays fixed) --}}
            <div class="rounded-xl border border-amber-300 p-5 bg-amber-50 flex flex-col gap-3
                        dark:border-amber-500/30 dark:bg-amber-500/5">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📅</span>
                    <span class="font-semibold text-sm text-travertine-900 dark:text-white">Events Calendar</span>
                </div>
                <p class="text-sm leading-relaxed text-travertine-600 dark:text-zinc-300">
                    A community-driven events calendar. Anyone with an account can add upcoming events. Filter by type
                    (streams vs open tournaments) and browse past events too.
                </p>
                <p class="text-sm leading-relaxed text-travertine-600 dark:text-zinc-300">
                    <span class="font-medium text-travertine-900 dark:text-white">Email reminders</span> — opt in to get notified 30 minutes before an event starts, separately for streams and open tournaments. Configurable in your account settings.
                </p>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-travertine-300 dark:border-zinc-700"></div>

        {{-- Support — ko-fi button stays as brand artwork --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Support</h2>
            <p class="leading-relaxed text-travertine-600 dark:text-zinc-300">
                Rankgim is free and always will be. If you find it useful and want to help keep the servers running, you
                can buy me a coffee.
            </p>
            <a href="https://ko-fi.com/rankgim" target="_blank"
                class="group relative flex items-center justify-center gap-3 rounded-xl px-6 py-5 overflow-hidden transition-all duration-300 hover:scale-[1.01] hover:shadow-lg hover:shadow-pink-500/20"
                style="background: linear-gradient(135deg, #ff5e5b 0%, #ff2d55 40%, #d63384 100%);">
                <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-all duration-300"></div>
                <svg class="w-8 h-8 text-white shrink-0 relative z-10 group-hover:animate-bounce" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M23.881 8.948c-.773-4.085-4.859-4.593-4.859-4.593H.723c-.604 0-.679.798-.679.798s-.082 7.324-.022 11.822c.164 2.424 2.586 2.672 2.586 2.672s8.267-.023 11.966-.049c2.438-.426 2.683-2.566 2.658-3.734 4.352.24 7.422-2.831 6.649-6.916zm-11.062 3.511c-1.246 1.453-4.011 3.976-4.011 3.976s-.121.119-.31.023c-.076-.057-.108-.09-.108-.09-.443-.441-3.368-3.049-4.034-3.954-.709-.965-1.041-2.7-.091-3.71.951-1.01 3.005-1.086 4.363.407 0 0 1.565-1.782 3.468-.963 1.903.82 1.832 3.011.723 4.311zm6.173.478c-.928.116-1.682.028-1.682.028V7.284h1.77s1.971.551 1.971 2.638c0 1.913-.985 2.667-2.059 3.015z" />
                </svg>
                <span class="text-white text-xl font-bold relative z-10">Help keep Rankgim alive ❤️</span>
            </a>
        </div>

        {{-- Credits --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Acknowledgements</h2>
            <p class="text-sm leading-relaxed text-travertine-600 dark:text-zinc-300">
                Special thanks to
                <span class="font-semibold text-travertine-900 dark:text-white">
                    <img class="inline w-5 h-3.5 rounded-sm mx-1 align-text-bottom" src="{{ asset('images/country_flags/se.svg') }}" />GruGloG
                </span>
                and
                <span class="font-semibold text-travertine-900 dark:text-white nowrap">
                    <img class="inline w-5 h-3.5 rounded-sm mx-1 align-text-bottom" src="{{ asset('images/country_flags/pl.svg') }}" />Ziggy
                </span>
                for submitting game results to the database — 577 and 317 games added respectively.
            </p>
        </div>

        {{-- Dedication — IM Fell English, artwork section, stays dark in both themes (rule #3) --}}
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-zinc-100" style="font-family: 'IM Fell English', serif;">
                To the people who made those years unforgettable
            </h2>
            <div class="rounded-xl border border-zinc-300 dark:border-zinc-700 p-8 bg-zinc-50 dark:bg-zinc-900/40">
                <div class="flex flex-col items-center gap-6 max-w-2xl mx-auto">

                    <p style="font-family: 'IM Fell English', serif;"
                        class="text-base text-center leading-relaxed italic max-w-lg text-zinc-600 dark:text-zinc-300">
                        For the hours we spent playing together, the conversations that were longer than the games, and
                        everything in between — those were some of the best years of my life. It was a great honour, and
                        I miss you all.
                    </p>

                    <div class="w-8 border-t border-zinc-300 dark:border-zinc-700"></div>

                    <div class="flex flex-col gap-7 w-full">

                        @foreach([
                            ['For infinite hours of talks and thousands of games on Europe &amp; ICCup — as teammates and rivals',
                             ['Moagim','MofD)Respect','PzH.MT','Azoun','Hukk[bwd]','Havoc_','Sadeff','aFF]oOKiVoo[','Darkelf-','Kat[tr] aka KatY-','Effka[pG]','Driver[soasc]','fallen.apollyon','ALK_aida','wiz[WL]ard','Super-Nova[TT]','GreenGosu','[aSc]Arhon','sFv.Dibuk','m4rlin','Bzium','wow.Seru_','agipek']],
                            ['For the LAN parties and real-life memories',
                             ['Kebes','Apogeum','Redeyes','FuriaKutasów','Sprite','peR aka eRa','aFF]Squizen[','aFF]Amlacz[','Welder']],
                            ['For being the great clanmates',
                             ['aFF]WolF[','aFF]Rasowy[','aFF]sluslu[','aFF]Mido[','aFF]money[','aFF]Surgeon[','aFF]Sfiesu[','aFF]Borek[','aFF]Sneazel[']],
                            ['For being eternally online on POL-1',
                             ['metody[watb]']],
                            ['For being part of my StarCraft life — and sometimes my real one',
                             ['Arcanos','LadiesFirst','MMoreAndMore','FurryDrone','Taehee','KubciO','Shocker_40','Fosken','Radley','Bonyth','Koget','Yeti','ZZZero']],
                        ] as [$caption, $names])
                        <div class="flex flex-col items-center gap-2">
                            <span style="font-family: 'IM Fell English', serif;"
                                class="text-xs uppercase tracking-widest text-zinc-400 dark:text-zinc-600 text-center">
                                {!! $caption !!}
                            </span>
                            <div class="flex flex-wrap justify-center gap-x-4 gap-y-1">
                                @foreach($names as $friend)
                                <span style="font-family: 'IM Fell English', serif;"
                                    class="text-sm text-zinc-500 dark:text-zinc-300">{{ $friend }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                    </div>

                    <div class="w-8 border-t border-zinc-300 dark:border-zinc-700"></div>

                    <p style="font-family: 'IM Fell English', serif;"
                        class="text-base italic text-center leading-relaxed text-zinc-500 dark:text-zinc-400">
                        …and the rest of
                        <span class="not-italic text-zinc-600 dark:text-zinc-300">StarCraft POL-1 @ Europe</span>,
                        <span class="not-italic text-zinc-600 dark:text-zinc-300">Netwars.pl</span>,
                        teams
                        <span class="not-italic text-zinc-600 dark:text-zinc-300">aFF · T4 · [MiB] · PzM · [WL]</span>
                        — plus everyone I forgot, because I'm old and bad with names.
                        <br class="hidden sm:block">
                        If you see this and feel you should be here, don't hesitate to reach out.
                    </p>

                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-travertine-300 dark:border-zinc-700"></div>

        {{-- Author --}}
        <div class="flex flex-col gap-3">
            <h2 class="text-xl font-semibold text-travertine-900 dark:text-white">Author</h2>
            <div class="flex flex-col gap-2">
                <p class="leading-relaxed text-travertine-600 dark:text-zinc-300">
                    Built by <span class="font-semibold text-travertine-900 dark:text-white">poegim</span> — somewhere between
                    StarCraft and real life since 1999. Also known as
                </p>
                <p class="text-sm font-mono leading-relaxed text-travertine-500 dark:text-zinc-300">
                    Krakow · aFF]ZuluNation · PzM.Zimbabwe · Poezja[T4] · iloveania · zulu[WL]Nation
                </p>
                <p class="leading-relaxed text-travertine-600 dark:text-zinc-300">
                    To get in touch, find me as <span class="font-semibold text-travertine-900 dark:text-white">poegim</span>
                    on Netwars, TeamLiquid or Battle.net.
                </p>
            </div>
        </div>

        {{-- Tech stack --}}
        <div class="flex flex-col gap-2">
            <h3 class="text-xs font-semibold uppercase tracking-widest text-travertine-500 dark:text-zinc-500">Built with</h3>
            <p class="text-xs font-mono text-travertine-400 dark:text-zinc-400">
                {{ implode(' · ', ['Laravel 12', 'Livewire', 'Flux UI', 'Alpine.js', 'Tailwind CSS', 'ApexCharts', 'MySQL']) }}
            </p>
        </div>

    </div>
</x-layouts::app>