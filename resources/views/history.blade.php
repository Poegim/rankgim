<x-layouts::app :title="__('Stats')">
    <div class="flex flex-col gap-4 w-full">

        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Stats</h1>
            <p class="text-sm text-zinc-500 mt-1">Records, history and interesting stats</p>
        </div>

        {{-- Race matchups --}}
        <livewire:dashboard.race-matchups />

        {{-- Yearly charts --}}
        <livewire:dashboard.yearly-charts />

        {{-- Recent games + Recent tournaments --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <livewire:dashboard.recent-games />
            <livewire:dashboard.recent-tournaments />
        </div>

        {{-- Spread chart --}}
        <livewire:dashboard.spread-chart />

        {{-- Top rivalries --}}
        <livewire:dashboard.top-rivalries />

        {{-- History components --}}
        <livewire:history.podium />
        <livewire:history.top10-time />
        <livewire:history.ranking-chart />
        <livewire:highest-peaks />

    </div>
</x-layouts::app>