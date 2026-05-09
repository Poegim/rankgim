<div class="rounded-xl p-3 sm:p-5
            border border-travertine-300 dark:border-zinc-700/60
            bg-travertine-50 dark:bg-zinc-800/40">
            
    {{-- Header — same pattern as every other dashboard widget --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-500">
            📅 Upcoming events
        </p>
        <a href="{{ route('events.index') }}"
           class="text-xs text-zinc-400 hover:text-zinc-200 transition-colors"
           wire:navigate>
            View all →
        </a>
    </div>

    {{-- Event cards — stacked, no scroll, no arrows --}}
    <div class="flex flex-col gap-3">
        @foreach($this->events as $event)
            <x-event-card :event="$event" />
        @endforeach
    </div>

</div>