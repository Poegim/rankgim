<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Notifications')" :subheading="__('Manage your email notification preferences')">
        <div class="flex items-center justify-between">
    <div>
        <p class="text-sm font-medium text-white">{{ __('Event reminders') }}</p>
        <p class="text-xs text-zinc-500 mt-0.5">{{ __('Receive an email 30 minutes before an event starts') }}</p>
    </div>
    <flux:switch wire:model.live="eventReminders" wire:change="updateEventReminders" />
</div>

{{-- Sub-options, visible only when reminders are on --}}
@if($eventReminders)
<div class="ml-4 pl-4 border-l border-zinc-700 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-zinc-300">{{ __('Streams') }}</p>
            <p class="text-xs text-zinc-500 mt-0.5">{{ __('Live streams and watch-only events') }}</p>
        </div>
        <flux:switch wire:model.live="eventRemindersStream" wire:change="updateEventRemindersStream" />
    </div>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-zinc-300">{{ __('Open tournaments') }}</p>
            <p class="text-xs text-zinc-500 mt-0.5">{{ __('Events you can register and play in') }}</p>
        </div>
        <flux:switch wire:model.live="eventRemindersOpen" wire:change="updateEventRemindersOpen" />
    </div>
</div>
@endif
        
    </x-settings.layout>
</section>