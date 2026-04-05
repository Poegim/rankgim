<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>
            <flux:sidebar.nav>
                <flux:sidebar.group class="grid">
                    <livewire:player-search />
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="trophy" :href="route('rankings.index')" :current="request()->routeIs('rankings.index')" wire:navigate>
                        {{ __('Ranking') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="globe-europe-africa" :href="route('countries.index')" :current="request()->routeIs('countries.index')" wire:navigate>
                        {{ __('Countries') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('tournaments.index')" :current="request()->routeIs('tournaments.index')" wire:navigate>
                        {{ __('Tournaments') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="queue-list" :href="route('games.index')" :current="request()->routeIs('games.index')" wire:navigate>
                        {{ __('Games') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('players.index')" :current="request()->routeIs('players.index')" wire:navigate>
                        {{ __('Players') }}
                    </flux:sidebar.item>
                    
                    @php
                        $upcomingEvents = \App\Models\Event::where('starts_at', '>=', now())
                            ->orderBy('starts_at')
                            ->get();
                        $nextEvent = $upcomingEvents->first();
                        $badgeColor = null;
                        if ($nextEvent) {
                            $minutesLeft = now()->diffInMinutes($nextEvent->starts_at, false);
                            $badgeColor = match(true) {
                                $minutesLeft <= 60   => 'bg-red-500 text-white',
                                $minutesLeft <= 1440 => 'bg-amber-400 text-black',
                                default              => 'bg-zinc-600 text-zinc-300',
                            };
                        }
                    @endphp

                    <flux:sidebar.item icon="calendar-days" :href="route('events.index')" :current="request()->routeIs('events.*')" wire:navigate>
                        <div class="flex items-center justify-between w-full">
                            <span>Events</span>
                            @if($nextEvent && $badgeColor)
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ $badgeColor }}">
                                {{ $upcomingEvents->count() }}
                            </span>
                            @endif
                        </div>
                    </flux:sidebar.item>

                </flux:sidebar.group>

                
            </flux:sidebar.nav>
            <flux:spacer />
            {{-- Ko-fi support link --}}
            <div class="mx-3 mb-3">
                <a href="https://ko-fi.com/rankgim" target="_blank"
                   class="group relative flex flex-col items-center gap-1 rounded-lg px-3 py-3 overflow-hidden transition-all duration-300 hover:scale-[1.02] text-center"
                   style="background: linear-gradient(135deg, #ff5e5b 0%, #ff2d55 50%, #d63384 100%);">
                    <div class="absolute inset-0 bg-white/0 group-hover:bg-white/10 transition-all duration-300"></div>
                    <svg class="w-6 h-6 text-white shrink-0 relative z-10 group-hover:animate-bounce" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.881 8.948c-.773-4.085-4.859-4.593-4.859-4.593H.723c-.604 0-.679.798-.679.798s-.082 7.324-.022 11.822c.164 2.424 2.586 2.672 2.586 2.672s8.267-.023 11.966-.049c2.438-.426 2.683-2.566 2.658-3.734 4.352.24 7.422-2.831 6.649-6.916zm-11.062 3.511c-1.246 1.453-4.011 3.976-4.011 3.976s-.121.119-.31.023c-.076-.057-.108-.09-.108-.09-.443-.441-3.368-3.049-4.034-3.954-.709-.965-1.041-2.7-.091-3.71.951-1.01 3.005-1.086 4.363.407 0 0 1.565-1.782 3.468-.963 1.903.82 1.832 3.011.723 4.311zm6.173.478c-.928.116-1.682.028-1.682.028V7.284h1.77s1.971.551 1.971 2.638c0 1.913-.985 2.667-2.059 3.015z"/>
                    </svg>
                    <span class="text-white text-sm font-semibold relative z-10">Help keep Rankgim alive ❤️</span>
                </a>
            </div>
            
            <flux:sidebar.item icon="information-circle" :href="route('about')" :current="request()->routeIs('about')" wire:navigate>
                {{ __('About') }}
            </flux:sidebar.item>
            @auth
                @if(auth()->user()->isAdmin())
                    <flux:sidebar.item icon="shield-check" :href="route('admin.index')" :current="request()->routeIs('admin.*')" wire:navigate>
                        {{ __('Admin Panel') }}
                    </flux:sidebar.item>
                @endif
                <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            @endauth
            @guest
                <flux:sidebar.nav>
                    <flux:sidebar.item icon="arrow-left-start-on-rectangle" href="{{ route('login') }}" wire:navigate>
                        {{ __('Login') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" href="{{ route('register') }}" wire:navigate>
                        {{ __('Register') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>
            @endguest
        </flux:sidebar>

        {{-- Mobile Header — always visible on mobile --}}
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            @auth
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
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
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                        >
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