@props(['guest'])

@php
    $colorClasses = match($guest['race'] ?? null) {
        'Terran'  => 'text-blue-400',
        'Zerg'    => 'text-purple-400',
        'Protoss' => 'text-yellow-400',
        default   => 'text-zinc-300',
    };
    $countryCode = strtolower($guest['country_code'] ?? 'kr');
@endphp

<span class="inline-flex items-center text-xs {{ $colorClasses }}">
    <img src="{{ asset('images/country_flags/' . $countryCode . '.svg') }}"
         class="w-4 h-3 mr-1 rounded-sm shrink-0"
         alt="{{ $countryCode }}">
    {{ $guest['name'] }}
</span>