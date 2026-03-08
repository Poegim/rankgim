<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="mb-2 p-2 bg-gray-100 dark:bg-neutral-900">
                    <h1 class="text-lg font-semibold">
                        {{ __('Top 5 Players') }}
                    </h1>
                </div>
                <div>
                    @foreach ($players as $player)
                        <div class="flex items-center gap-1 px-4 py-1 hover:bg-gray-100 dark:hover:bg-neutral-900">
                        <div class="h-8 w-8 rounded-full mr-2 overflow-hidden bg-gray-300 dark:bg-neutral-700">
                            <img 
                                src="{{ asset('/storage/images/country_flags/' . strtolower($player->country_code) . '.svg') }}" 
                                alt="{{ strtolower($player->country_code) }}" 
                                class="h-full w-full object-cover"
                            >
                        </div>
                            <div>
                                <p class="text-sm font-medium">
                                    {{ $player->name }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ $player->race }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <div class="mb-2 p-2 bg-gray-100 dark:bg-neutral-900">
                    <h1 class="text-lg font-semibold">
                        {{ __('Last 5 Tournaments') }}
                    </h1>
                </div>
                <div>
                    @foreach ($tournaments as $tournament)
                        <div class="flex items-center gap-1 px-4 py-1 hover:bg-gray-100 dark:hover:bg-neutral-900">
                            <div>
                                <p class="text-sm font-bold p-2 italic">
                                    {{ $tournament->name }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
