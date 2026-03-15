<x-layouts::app :title="__('Compare Players')">
    <div class="py-12 px-4">
        <livewire:players.compare :id1="(int) $id1" :id2="(int) $id2" />
    </div>
</x-layouts::app>