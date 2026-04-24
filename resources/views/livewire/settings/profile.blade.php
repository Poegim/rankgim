<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name, location and profile information')">

        {{-- Profile photo --}}
        <div class="flex items-center gap-4 my-6">
            <flux:avatar
                size="xl"
                :src="auth()->user()->profilePhotoUrl()"
                :name="auth()->user()->name"
                color="auto"
                :color:seed="auth()->user()->id"
            />
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <flux:input wire:model="photo" type="file" accept="image/*" size="sm" />
                    @if(auth()->user()->profile_photo_path)
                        <flux:button wire:click="removePhoto" wire:confirm="Remove profile photo?" variant="danger" size="sm">
                            Remove
                        </flux:button>
                    @endif
                </div>
                @error('photo') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                <flux:text class="text-xs">JPG, PNG or GIF · max 1MB</flux:text>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">

            {{-- Name --}}
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            {{-- Email --}}
            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />
                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}
                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ── Location section ─────────────────────────────── --}}
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-5 space-y-4">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Flag / Location</p>

                {{-- Country picker with live flag preview --}}
                <div
                    x-data="{ code: @entangle('countryCode') }"
                    class="space-y-1"
                >
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Select prefered flag</label>
                    <div class="flex items-center gap-3">
                        {{-- Flag preview --}}
                        <div class="w-9 h-6 rounded-sm overflow-hidden bg-zinc-200 dark:bg-zinc-700 shrink-0 flex items-center justify-center">
                            <template x-if="code">
                                <img :src="`/images/country_flags/${code.toLowerCase()}.svg`" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!code">
                                <span class="text-zinc-400 text-xs">?</span>
                            </template>
                        </div>
                        <select
                            wire:model.live="countryCode"
                            x-model="code"
                            class="flex-1 rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500"
                        >
                            <option value="">— No country selected —</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country['code'] }}" {{ $countryCode === $country['code'] ? 'selected' : '' }}>
                                    {{ $country['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('countryCode') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- City autocomplete --}}
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">City</label>

                    @if($citySelected && $city)
                        {{-- Selected state: show pill with clear button --}}
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/30 text-sm text-amber-300">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $city }}</span>
                            </div>
                            <button type="button" wire:click="clearCity" class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors">
                                Change
                            </button>
                        </div>
                    @else
                        {{-- Search input with dropdown --}}
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="citySearch"
                                wire:input="searchCity"
                                placeholder="Type a city name…"
                                autocomplete="off"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500"
                            >

                            {{-- Suggestions dropdown --}}
                            @if(count($citySuggestions) > 0)
                                <div class="absolute z-20 w-full mt-1 rounded-lg border border-zinc-600 bg-zinc-800 shadow-xl overflow-hidden">
                                    @foreach($citySuggestions as $i => $suggestion)
                                        <button
                                            type="button"
                                            wire:click="selectCity({{ $i }})"
                                            class="w-full text-left px-3 py-2.5 text-sm text-zinc-200 hover:bg-zinc-700 transition-colors flex items-center gap-2 border-b border-zinc-700/50 last:border-b-0"
                                        >
                                            <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            <span class="truncate">{{ $suggestion['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @elseif(strlen($citySearch) >= 2 && !$citySelected)
                                <div class="absolute z-20 w-full mt-1 rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2.5 text-xs text-zinc-500">
                                    No cities found — try a different spelling
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Bio ─────────────────────────────────────────────── --}}
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-5">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">About</p>
                <div
                    x-data="{ len: {{ strlen(auth()->user()->bio ?? '') }} }"
                    class="space-y-1"
                >
                    <textarea
                        wire:model="bio"
                        x-on:input="len = $el.value.length"
                        maxlength="280"
                        rows="3"
                        placeholder="A few words about yourself…"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-sm px-3 py-2 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 resize-none"
                    ></textarea>
                    <div class="flex justify-end">
                        <span class="text-xs text-zinc-400 font-mono" x-text="`${len}/280`"></span>
                    </div>
                </div>
                @error('bio') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <x-action-message class="me-3" on="profile-updated">{{ __('Saved.') }}</x-action-message>
            </div>

        </form>
    </x-settings.layout>
</section>