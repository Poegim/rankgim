<x-layouts::app :title="__('Compare Players')">
    <x-slot name="title">Compare Players</x-slot>

    <div class="py-12 px-4">
        <div class="max-w-xl mx-auto mb-10 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Compare Players</h1>
            <p class="text-zinc-400">Select two players to see head-to-head stats and rating history</p>
        </div>

        <livewire:players.compare-search />
    </div>
</x-layouts::app>