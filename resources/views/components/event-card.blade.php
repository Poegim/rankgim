@props(['event'])

<div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 p-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">

    {{-- Lewa strona: nazwa, opis, gracze, linki --}}
    <div class="flex flex-col gap-2 min-w-0 overflow-hidden">

        {{-- Badge'e + nazwa eventu --}}
        <div class="flex items-center gap-2">
            <x-event-badges :event="$event" />
            <p class="text-sm sm:text-base font-bold text-zinc-900 dark:text-white truncate">
                {{ $event->name }}
            </p>
        </div>

        {{-- Opis --}}
        @if($event->description)
            <p class="text-xs text-zinc-500 truncate">{{ $event->description }}</p>
        @endif

        {{-- Lista graczy --}}
        <x-event-players :event="$event" />

        {{-- Linki + registration --}}
        <x-event-links :event="$event" />
    </div>

    {{-- Prawa strona: data + countdown --}}
    <x-event-countdown :starts-at="$event->starts_at" :is-stream="$event->isStream()" />
</div>