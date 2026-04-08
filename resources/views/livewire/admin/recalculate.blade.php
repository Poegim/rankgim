<div>

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-4">
        <flux:heading class="text-lg font-semibold mb-1">Stats insights</flux:heading>
        <flux:text class="mt-1 text-zinc-500">View detailed statistics and insights about player performance.</flux:text>

        <div class="my-4">
            <a href="{{ route('admin.achievement-insights') }}" class=" my-6 rounded bg-amber-600 py-2 px-6"> View Insights
            </a>
        </div>
        </div>

    <div
        x-data="{ show: false }"
        x-on:recalculated.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Ratings recalculated
    </div>



    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
        <flux:heading class="text-lg font-semibold mb-1">DISABLED</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Recalculates all player ratings from scratch based on game history.</flux:text>

        @if($lastRun)
            <p class="text-sm text-zinc-400 mt-2">Last run: {{ $lastRun }}</p>
        @endif

        <div class="mt-4">
            <flux:button
                disabled
                variant="danger"
                wire:click="recalculate"
                wire:loading.attr="disabled"
                wire:target="recalculate">
                <span wire:loading.remove wire:target="recalculate">⚙️ Recalculate All Ratings</span>
                <span wire:loading wire:target="recalculate">Processing...</span>
            </flux:button>
        </div>
    </div>
</div>