{{-- ══════════════════════════════════════════════════════════════════════
     Guest-only honeytrap modal.

     Looks identical to the real currency picker (used in Forecast\Index) but
     does NOT create anything on the backend. Every interactive element
     (faction tile, "Lock it in" button, even close X) redirects a guest to
     /login. The goal is purely to spark curiosity and pull new users in.

     How to open it:
         dispatch a window event — `$dispatch('open-guest-wallet-modal')`.
     That keeps this modal fully decoupled from any Livewire component and
     lets any blade anywhere (dashboard, forecast page, etc.) open it with
     zero wiring.

     Auth-aware: if a logged-in user somehow fires the event, this modal
     stays hidden — the real Livewire-backed modal is used instead.
     ══════════════════════════════════════════════════════════════════════ --}}

@guest
    <div
        x-data="{ open: false, selected: 'minerals' }"
        x-on:open-guest-wallet-modal.window="open = true"
        x-on:keydown.escape.window="open = false"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        x-on:click.self="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-zinc-900 border border-zinc-700 rounded-xl w-full max-w-md"
        >
            <div class="flex items-center justify-between px-5 py-3 border-b border-zinc-700/50">
                <h2 class="text-lg font-semibold text-white">Pick your faction</h2>
                <button type="button" x-on:click="open = false" class="text-zinc-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-2">
                <p class="text-xs text-zinc-500 mb-4">Each faction gives you a different edge. You can switch once per season.</p>

                @foreach(\App\Models\ForecastWallet::CURRENCIES as $key => $currency)
                    {{-- Clicking a faction "selects" it visually (Alpine state), but
                         the inner CTA actually goes to login. We keep the selection
                         mechanic so the UI feels real while the user browses. --}}
                    <button
                        type="button"
                        x-on:click="selected = '{{ $key }}'"
                        class="w-full flex items-center gap-3 p-3 rounded-lg border transition-colors text-left"
                        :class="selected === '{{ $key }}'
                            ? 'bg-zinc-700 border-zinc-500 text-white'
                            : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-300 hover:border-zinc-600'"
                    >
                        <span class="text-2xl">{{ $currency['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold">{{ $currency['label'] }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ $currency['bonus'] }}</p>
                        </div>
                        <span x-show="selected === '{{ $key }}'" class="ml-auto text-emerald-400 text-xs">✓</span>
                    </button>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 px-5 py-3 border-t border-zinc-700/50">
                <button type="button" x-on:click="open = false"
                    class="px-4 py-2 text-sm text-zinc-400 hover:text-white transition-colors">
                    Cancel
                </button>
                {{-- The "confirm" action funnels straight to login — that's the hook. --}}
                <a href="{{ route('login') }}" wire:navigate
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                    Lock it in
                </a>
            </div>
        </div>
    </div>
@endguest