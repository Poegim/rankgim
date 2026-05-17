{{--
    Admin panel layout — same pattern as components/settings/layout.blade.php.
    Left navlist with tab links, right side renders the current section's content.
--}}
<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Admin') }}">
            <flux:navlist.item
                icon="users"
                :href="route('admin.index')"
                :current="request()->routeIs('admin.index')"
                wire:navigate>
                {{ __('Users') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="video-camera"
                :href="route('admin.streams')"
                :current="request()->routeIs('admin.streams')"
                wire:navigate>
                {{ __('Streams') }}
            </flux:navlist.item>

            <flux:navlist.item
                icon="user-minus"
                :href="route('admin.inactive-players')"
                :current="request()->routeIs('admin.inactive-players')"
                wire:navigate>
                {{ __('Inactive Players') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6 min-w-0">
        <flux:heading size="xl" level="1">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ $subheading ?? '' }}</flux:subheading>
        <flux:separator variant="subtle" class="mb-6" />

        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
