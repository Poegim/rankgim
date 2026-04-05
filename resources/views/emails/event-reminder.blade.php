<x-mail::message>
# {{ $event->name }} starts in 30 minutes!

@if($event->description)
{{ $event->description }}

@endif
**When:** {{ $event->startsAtCET()->format('d.m.Y H:i') }} CET

<x-mail::button :url="route('events.index')">
View event
</x-mail::button>

See you there! 🏓<br>
{{ config('app.name') }}
</x-mail::message>