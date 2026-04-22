@props(['event'])

@php
    $links = $event->parsedLinks();
    $registrationOpen = $event->isRegistrationOpen();
@endphp

@if($links || $registrationOpen)
    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-1.5">
        @foreach($links as $link)
            <a href="{{ $link['url'] }}"
               target="_blank"
               rel="noopener"
               class="flex sm:inline-flex items-center justify-center sm:justify-start px-2.5 py-2 sm:py-0.5 rounded text-xs font-medium transition-opacity hover:opacity-80"
               style="background: {{ $link['color'] }}20; color: {{ $link['color'] }}; border: 1px solid {{ $link['color'] }}40">
                {{ $link['label'] ?: ucfirst($link['type']) }}
            </a>
        @endforeach

        @if($registrationOpen)
            <span class="flex sm:inline-flex items-center justify-center sm:justify-start gap-1.5 px-2.5 py-2 sm:py-0.5 rounded text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/25">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                Registration open
            </span>
        @endif
    </div>
@endif