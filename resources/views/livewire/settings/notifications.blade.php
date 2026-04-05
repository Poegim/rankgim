<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Notifications')" :subheading="__('Manage your email notification preferences')">
        <div class="my-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white">{{ __('Event reminders') }}</p>
                    <p class="text-xs text-zinc-500 mt-0.5">{{ __('Receive an email 30 minutes before an event starts') }}</p>
                </div>
                <flux:switch wire:model.live="eventReminders" wire:change="updateEventReminders" />
            </div>
        </div>
    </x-settings.layout>
</section>