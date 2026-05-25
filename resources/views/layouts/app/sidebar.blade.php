<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      >

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-travertine-75 dark:bg-zinc-800">
    @php
        // Sidebar "Streams" row — three independent badges:
        //   ⭐ starred live   — user's favorited streamers currently broadcasting (always shown for guests as 0 = soft signup nudge)
        //   🟣 Twitch live    — featured Twitch streams (hidden when 0)
        //   🅢 SOOP live      — featured SOOP streams  (hidden when 0)
        //
        // Each badge counts only Featured (whitelisted + promoted favorites), not the long-tail "Other" pool.
        // We pull the merged Featured list once and bucket by platform/favorite flag — single aggregator call.
        $featuredAll          = app(\App\Services\Streams\LiveStreamsService::class)
            ->featuredStreams(null, auth()->user());

        $starredLiveCount     = 0;
        $twitchFeaturedCount  = 0;
        $soopFeaturedCount    = 0;

        foreach ($featuredAll as $_row) {
            if (! empty($_row['is_favorite'])) {
                $starredLiveCount++;
            }
            if (($_row['platform'] ?? null) === 'twitch') {
                $twitchFeaturedCount++;
            } elseif (($_row['platform'] ?? null) === 'soop') {
                $soopFeaturedCount++;
            }
        }
        // Compute the next upcoming event and its "urgency" badge color for the Events item.
        $upcomingEvents = \App\Models\Event::where('starts_at', '>=', now()->subHours(\App\Models\Event::LIVE_WINDOW_HOURS))
            ->where('starts_at', '<=', now()->addDays(7))
            ->orderBy('starts_at')
            ->get();

        $nextEvent  = $upcomingEvents->first();
        $badgeColor = null;

        if ($nextEvent) {
            $minutesLeft = now()->diffInMinutes($nextEvent->starts_at, false);
            $badgeColor = match (true) {
                $minutesLeft <= 60   => 'bg-red-500 text-white',
                $minutesLeft <= 1440 => 'bg-amber-400 text-black',
                default              => 'bg-travertine-300 text-travertine-700 dark:bg-zinc-600 dark:text-zinc-300',
            };
        }

        // Compute upcoming open forecast matches and urgency badge color.
        $season = \App\Models\ForecastSeason::current();

        $upcomingForecasts    = collect();
        $nextForecast         = null;
        $forecastBadgeColor   = null;

        if ($season) {
            $upcomingForecasts = \App\Models\ForecastMatch::where('season_id', $season->id)
                ->open()
                ->where('scheduled_at', '<=', now()->addDays(7))
                ->orderBy('scheduled_at')
                ->get();

            $nextForecast = $upcomingForecasts->first();

            if ($nextForecast) {
                $minutesLeft = now()->diffInMinutes($nextForecast->scheduled_at, false);
                $forecastBadgeColor = match (true) {
                    $minutesLeft <= 120  => 'bg-red-500 text-white',
                    $minutesLeft <= 1440 => 'bg-amber-400 text-black',
                    default              => 'bg-travertine-300 text-travertine-700 dark:bg-zinc-600 dark:text-zinc-300',
                };
            }
        }
    @endphp

    {{-- Sidebar — parchment-deep surface, distinct from body sand bg --}}
    <flux:sidebar sticky collapsible="mobile"
        class="border-e
               border-travertine-300 bg-travertine-100
               dark:border-zinc-700 dark:bg-zinc-900">

        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>

            <flux:sidebar.group class="grid">
                <livewire:player-search />

                <flux:sidebar.item icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="trophy"
                    :href="route('rankings.index')"
                    :current="request()->routeIs('rankings.index')"
                    wire:navigate>
                    {{ __('Ranking') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Briefing">
                <flux:sidebar.item icon="video-camera"
                    :href="route('streams.index')"
                    :current="request()->routeIs('streams.index')"
                    wire:navigate>
                    <div class="flex items-center justify-between w-full gap-2">
                        <span class="truncate">{{ __('Streams') }}</span>

                        {{-- Three badge cluster: starred / Twitch / SOOP. --}}
                        {{-- Starred always visible (drives signup for guests). Platform badges hide at 0. --}}
                        <span class="inline-flex items-center gap-1 shrink-0">
                            {{-- Starred badge: gold star + count. Greyed when 0 (guest or no favorites live). --}}
                            <span
                                class="inline-flex items-center gap-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                       {{ $starredLiveCount > 0 ? 'bg-amber-500/20 text-amber-300' : 'bg-zinc-800 text-zinc-600' }}"
                                title="{{ auth()->check()
                                    ? __('Your favorite streamers currently live')
                                    : __('Sign in to favorite streamers and see them here') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5">
                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                </svg>
                                {{ $starredLiveCount }}
                            </span>

                            @if ($twitchFeaturedCount > 0)
                                <span
                                    class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
                                    style="background-color: #9146ff;"
                                    title="{{ __('Featured Twitch streams currently live') }}"
                                >
                                    {{ $twitchFeaturedCount }}
                                </span>
                            @endif

                            @if ($soopFeaturedCount > 0)
                                <span
                                    class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
                                    style="background-color: #ef4444;"
                                    title="{{ __('Featured SOOP streams currently live') }}"
                                >
                                    {{ $soopFeaturedCount }}
                                </span>
                            @endif
                        </span>
                    </div>
                </flux:sidebar.item>
                <flux:sidebar.item icon="calendar-days"
                    :href="route('events.index')"
                    :current="request()->routeIs('events.*')"
                    wire:navigate>
                    <div class="flex items-center justify-between w-full">
                        <span>{{ __('Events') }}</span>
                        @if($nextEvent && $badgeColor)
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ $badgeColor }}">
                                {{ $upcomingEvents->count() }}
                            </span>
                        @endif
                    </div>
                </flux:sidebar.item>

                <flux:sidebar.item icon="bolt"
                    :href="route('forecast.index')"
                    :current="request()->routeIs('forecast.*')"
                    wire:navigate>
                    <div class="flex items-center justify-between w-full">
                        <span>{{ __('Koprulu Forecast') }}</span>
                        @if($nextForecast && $forecastBadgeColor)
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ $forecastBadgeColor }}">
                                {{ $upcomingForecasts->count() }}
                            </span>
                        @endif
                    </div>
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Insights">
                <flux:sidebar.item icon="star"
                    :href="route('achievements.index')"
                    :current="request()->routeIs('achievements.index')"
                    wire:navigate>
                    {{ __('Achievements') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="chart-bar"
                    :href="route('stats.index')"
                    :current="request()->routeIs('stats.index')">
                    {{ __('Stats') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Browse">
                <flux:sidebar.item icon="users"
                    :href="route('players.index')"
                    :current="request()->routeIs('players.*')"
                    wire:navigate>
                    {{ __('Players') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="globe-europe-africa"
                    :href="route('countries.index')"
                    :current="request()->routeIs('countries.*')"
                    wire:navigate>
                    {{ __('Countries') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="calendar-days"
                    :href="route('tournaments.index')"
                    :current="request()->routeIs('tournaments.*')"
                    wire:navigate>
                    {{ __('Tournaments') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="queue-list"
                    :href="route('games.index')"
                    :current="request()->routeIs('games.*')"
                    wire:navigate>
                    {{ __('Games') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group>
                <flux:sidebar.item icon="information-circle"
                    :href="route('about')"
                    :current="request()->routeIs('about')"
                    wire:navigate>
                    {{ __('About') }}
                </flux:sidebar.item>

                @auth
                    @if(auth()->user()->isAdmin())
                        <flux:sidebar.item icon="shield-check"
                            :href="route('admin.index')"
                            :current="request()->routeIs('admin.*')"
                            wire:navigate>
                            {{ __('Admin Panel') }}
                        </flux:sidebar.item>
                    @endif
                @endauth
            </flux:sidebar.group>

        </flux:sidebar.nav>

        <flux:spacer />

        {{-- Theme toggle button --}}
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun"></flux:radio>
            <flux:radio value="dark" icon="moon"></flux:radio>
            <flux:radio value="system" icon="computer-desktop"></flux:radio>
        </flux:radio.group>


        {{-- Ko-fi support — gradient bg, !text-white preserved across themes --}}
        <div class="mx-3">
            <a href="https://ko-fi.com/rankgim" target="_blank"
                class="group relative flex items-center gap-2 rounded-lg px-3 py-2 overflow-hidden transition-all duration-300 hover:scale-[1.02]"
                style="background: linear-gradient(135deg, #ff5e5b 0%, #ff2d55 50%, #d63384 100%);">
                <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-all duration-300"></div>
                <svg class="w-4 h-4 !text-white shrink-0 relative z-10 group-hover:animate-bounce"
                    viewBox="0 0 24 24" fill="currentColor">
                    <path d="M23.881 8.948c-.773-4.085-4.859-4.593-4.859-4.593H.723c-.604 0-.679.798-.679.798s-.082 7.324-.022 11.822c.164 2.424 2.586 2.672 2.586 2.672s8.267-.023 11.966-.049c2.438-.426 2.683-2.566 2.658-3.734 4.352.24 7.422-2.831 6.649-6.916zm-11.062 3.511c-1.246 1.453-4.011 3.976-4.011 3.976s-.121.119-.31.023c-.076-.057-.108-.09-.108-.09-.443-.441-3.368-3.049-4.034-3.954-.709-.965-1.041-2.7-.091-3.71.951-1.01 3.005-1.086 4.363.407 0 0 1.565-1.782 3.468-.963 1.903.82 1.832 3.011.723 4.311zm6.173.478c-.928.116-1.682.028-1.682.028V7.284h1.77s1.971.551 1.971 2.638c0 1.913-.985 2.667-2.059 3.015z" />
                </svg>
                <span class="!text-white text-xs font-semibold relative z-10 whitespace-nowrap">
                    Help keep Rankgim alive ❤️
                </span>
            </a>
        </div>


        {{-- Forecast wallet pill — card lift on parchment-deep sidebar --}}
        @auth
            @php
                $_forecastSeason = \App\Models\ForecastSeason::current();
                $sidebarWallet   = null;
                $sidebarPendingBets = 0;

                if ($_forecastSeason) {
                    $sidebarWallet = \App\Models\ForecastWallet::where('user_id', auth()->id())
                        ->where('season_id', $_forecastSeason->id)
                        ->withCount([
                            'predictions as pending_bets_count' => fn ($q) => $q->where('result', 'pending'),
                        ])
                        ->first();

                    $sidebarPendingBets = $sidebarWallet?->pending_bets_count ?? 0;
                }
            @endphp

            @if($sidebarWallet)
                @php
                    $sidebarIcon = \App\Models\ForecastWallet::CURRENCIES[$sidebarWallet->currency]['icon'] ?? '💠';
                @endphp
                <div class="mx-3 mb-1 px-3 py-2 rounded-lg
                            bg-travertine-50 border border-travertine-300
                            dark:bg-zinc-800/60 dark:border-zinc-700/30
                            flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="text-base leading-none shrink-0">{{ $sidebarIcon }}</span>
                        <span class="text-xs font-mono font-bold text-amber-300 leading-none">
                            {{ number_format($sidebarWallet->balance, 2) }}
                        </span>
                        <span class="text-[10px] leading-none
                                     text-travertine-500 dark:text-zinc-600">energy</span>
                    </div>

                    <span class="text-xs shrink-0
                                 text-travertine-400 dark:text-zinc-700">·</span>

                    <div class="flex items-center gap-1 shrink-0">
                        @if($sidebarPendingBets > 0)
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full text-[10px] font-bold leading-none
                                         bg-amber-200 text-amber-800
                                         dark:bg-amber-500/20 dark:text-amber-400">
                                {{ $sidebarPendingBets }}
                            </span>
                            <span class="text-[10px] leading-none
                                         text-travertine-600 dark:text-zinc-400">
                                active {{ Str::plural('bet', $sidebarPendingBets) }}
                            </span>
                        @else
                            <span class="text-[10px] leading-none
                                         text-travertine-500 dark:text-zinc-600">no active bets</span>
                        @endif
                    </div>
                </div>
            @endif
        @endauth


        @auth
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        @endauth

        @guest
            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-left-start-on-rectangle"
                    href="{{ route('login') }}" wire:navigate>
                    {{ __('Login') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="user-plus"
                    href="{{ route('register') }}" wire:navigate>
                    {{ __('Register') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        @endguest

    </flux:sidebar>

    {{-- ── Mobile Header ── --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <x-app-logo href="{{ route('dashboard') }}" />
        <flux:spacer />

        @auth
            <flux:dropdown position="top" align="end">
                <flux:profile :name="auth()->user()->name"
                    :src="auth()->user()?->profilePhotoUrl()"
                    icon-trailing="chevron-down" />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    size="xl"
                                    :src="auth()->user()->profilePhotoUrl()"
                                    :name="auth()->user()->name"
                                    color="auto"
                                    :color:seed="auth()->user()->id"
                                />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @endauth
    </flux:header>

    {{ $slot }}
    @fluxScripts
</body>

</html>