@props(['event'])

@php
    $hasPlayers = $event->players->isNotEmpty();
    $hasGuests = !empty($event->guest_players);
@endphp

@if($hasPlayers || $hasGuests)
    <div class="flex flex-wrap gap-x-3 gap-y-1.5">
        @foreach($event->players as $player)
            <x-event-player-link :player="$player" />
        @endforeach

        @foreach($event->guest_players ?? [] as $guest)
            <x-event-guest-player :guest="$guest" />
        @endforeach
    </div>
@endif