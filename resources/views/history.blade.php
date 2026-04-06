<x-layouts::app :title="__('History')">

    <div class="flex flex-col gap-8 max-w-6xl mx-auto px-4 py-8">

        {{-- Page header --}}
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">History</h1>
            <p class="text-sm text-zinc-500 mt-1">All-time records and ranking history</p>
        </div>

        {{-- Klocki --}}
        <livewire:history.podium />
        <livewire:history.ranking-chart />
        <livewire:highest-peaks />

    </div>

</x-layouts::app>