<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <div class="-m-6 lg:-m-8 p-3 lg:p-6">
            {{ $slot }}
        </div>
    </flux:main>

    <x-forecast.guest-wallet-modal />

    <livewire:comments.comment-section />
</x-layouts::app.sidebar>