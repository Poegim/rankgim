@props([
    'sidebar' => false,
])

@if($sidebar)
    {{-- Sidebar variant: hide the logo container div so there is no empty gap --}}
    <flux:sidebar.brand name="Rankgim" {{ $attributes }} class="[&>div:first-child]:hidden" />
@else
    {{-- Default variant: hide the logo container div so there is no empty gap --}}
    <flux:brand name="Rankgim" {{ $attributes }} class="[&>div:first-child]:hidden" />
@endif