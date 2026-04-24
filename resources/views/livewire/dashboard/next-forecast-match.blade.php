{{-- ══════════════════════════════════════════════════════════════════════
     Dashboard widget: closest upcoming forecast matches.

     Visual identity is shared with the /forecast page via the
     <x-forecast.match-card /> component (compact mode strips admin row +
     full prediction badge). This keeps a single source of truth for
     what a match "looks like" across the app.
     ══════════════════════════════════════════════════════════════════════ --}}

<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-5">

    {{-- Widget header — matches the dashboard widget contract
         (text-xs uppercase label + "View all →" link on the right) --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            🎯 Next up
        </h2>
        <a href="{{ route('forecast.index') }}" wire:navigate
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors">
            Pick &apos;em all →
        </a>
    </div>

    @if($this->matches->isEmpty())
        <div class="text-center py-8 text-zinc-600 text-sm">
            No open matches right now
        </div>
    @else
        <div class="space-y-3">
            @foreach($this->matches as $loopMatch)
                @php
                    // Resolve current user's prediction — predictions are eager-loaded,
                    // so this is an in-memory filter, not a DB hit.
                    $userPrediction = auth()->check()
                        ? $loopMatch->predictions->firstWhere('user_id', auth()->id())
                        : null;
                @endphp

                {{-- Each card links to /forecast as a whole when it's NOT interactive
                     (user already picked / logged out / locked). Interactive cards
                     keep per-side buttons that route to the forecast page via
                     wire:click -- but on the dashboard we don't have openBetModal
                     wired in, so we wrap the card in an anchor to /forecast. --}}
                <a href="{{ route('forecast.index') }}" wire:navigate
                   class="block group"
                   wire:key="dash-match-{{ $loopMatch->id }}">
                    <x-forecast.match-card
                        :match="$loopMatch"
                        :user-prediction="$userPrediction"
                        :can-manage-games="false"
                        :compact="true" />
                </a>
            @endforeach
        </div>
    @endif
</div>