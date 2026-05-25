<x-layouts::app>
    <x-admin.layout
        :heading="__('Admin · Inactive Players')"
        :subheading="__('Players with 15+ games who have not played recently')">

        <livewire:admin.inactive-ranking />
    </x-admin.layout>
</x-layouts::app>