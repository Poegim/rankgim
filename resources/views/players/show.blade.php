<x-layouts::app :title="__('Player')">
    <div class="w-full">
        <livewire:players.show :playerId="$playerId" />
    </div>
</x-layouts::app>