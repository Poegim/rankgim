<x-layouts::app>
    <x-admin.layout
        :heading="__('Admin · Users')"
        :subheading="__('Manage user roles, balances and view forecast activity')">

        {{-- Quick link to deeper insights — replaces what was in the old Recalculate widget --}}
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.achievement-insights') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 hover:bg-amber-500 transition-colors px-4 py-2 text-sm font-semibold text-white"
                wire:navigate>
                📊 {{ __('View Achievement Insights') }}
            </a>
        </div>

        <livewire:admin.users />
    </x-admin.layout>
</x-layouts::app>