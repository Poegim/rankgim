@props(['player'])

@php
    $colorClasses = match($player->race) {
        'Terran'  => 'text-blue-400 hover:text-blue-300',
        'Zerg'    => 'text-purple-400 hover:text-purple-300',
        'Protoss' => 'text-yellow-400 hover:text-yellow-300',
        default   => 'text-zinc-300 hover:text-zinc-200',
    };
@endphp

<a href="{{ route('players.show', ['id' => $player->id, 'slug' => $player->name]) }}"
   wire:navigate
   class="inline-flex items-center text-xs {{ $colorClasses }}">
    <img src="{{ asset('images/country_flags/' . strtolower($player->country_code) . '.svg') }}"
         class="w-4 h-3 mr-1 rounded-sm shrink-0"
         alt="{{ $player->country_code }}">
    {{ $player->name }}
</a>