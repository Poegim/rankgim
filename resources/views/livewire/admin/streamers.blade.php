<div class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold text-zinc-100">Streamers</h2>
        <p class="text-sm text-zinc-400">
            Whitelist of streamers surfaced on the public <a href="{{ route('streams.index') }}" class="text-rose-400 hover:underline">live streams</a> page.
            The scheduler fetches each platform's StarCraft category every 5 minutes; only entries listed below appear under "Featured".
        </p>
    </div>

    {{-- Platform tab strip --}}
    <div class="flex flex-wrap gap-1 border-b border-zinc-700/60 pb-1">
        @foreach ($platformTabs as $value => $label)
            @php
                $isActive = $platform === $value;
                $accent   = $value === 'twitch' ? '#9146ff' : '#ef4444';
                $style    = $isActive ? "background: {$accent}; color: white;" : '';
            @endphp
            <button
                type="button"
                wire:click="setPlatform('{{ $value }}')"
                @if ($style) style="{{ $style }}" @endif
                class="px-4 py-2 rounded-t-md text-xs font-semibold uppercase tracking-wider transition-colors
                    {{ ! $isActive ? 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700/40' : '' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Add new streamer form --}}
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 p-5">
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-widest text-zinc-500">
            Add {{ $platformTabs[$platform] }} streamer
        </h3>

        {{--
            Grid-based form row. Fixed column widths so the row reads as a clean record:
              [ user_id (flex) ][ label (flex) ][ race (160px) ][ Add (auto) ]
            Help text + error messages live BELOW the row so they never disturb alignment.
        --}}
        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_160px_auto] sm:items-end">
            <div>
                <label class="mb-1 block text-xs text-zinc-400" for="newUserId">user_id</label>
                <input
                    id="newUserId"
                    type="text"
                    wire:model="newUserId"
                    placeholder="{{ $this->userIdPlaceholder }}"
                    class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-rose-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="mb-1 block text-xs text-zinc-400" for="newLabel">Label</label>
                <input
                    id="newLabel"
                    type="text"
                    wire:model="newLabel"
                    placeholder="ASL Official"
                    class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:border-rose-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="mb-1 block text-xs text-zinc-400" for="newRace">Race</label>
                <select
                    id="newRace"
                    wire:model="newRace"
                    class="w-full rounded border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 focus:border-rose-500 focus:outline-none"
                >
                    <option value="">—</option>
                    @foreach ($races as $label)
                        <option value="{{ $label }}" class="bg-zinc-900 text-zinc-100">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button
                    type="button"
                    wire:click="add"
                    class="w-full rounded bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 sm:w-auto"
                >
                    Add
                </button>
            </div>
        </div>

        {{-- Help text + validation errors, kept BELOW the row so they don't break alignment. --}}
        <div class="mt-2 space-y-1 text-[11px]">
            <p class="text-zinc-500">{{ $this->userIdHelp }}</p>
            @error('newUserId')<p class="text-rose-400">user_id: {{ $message }}</p>@enderror
            @error('newLabel')<p class="text-rose-400">label: {{ $message }}</p>@enderror
            @error('newRace')<p class="text-rose-400">race: {{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Existing streamers list (per active platform tab) --}}
    <div class="overflow-hidden rounded-xl border border-zinc-700/60 bg-zinc-800/40">
        @if ($this->streamers->isEmpty())
            <div class="p-6 text-center text-sm text-zinc-400">
                No {{ $platformTabs[$platform] }} streamers registered yet. Add one above.
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-zinc-900/40 text-left text-xs uppercase tracking-widest text-zinc-500">
                    <tr>
                        <th class="px-4 py-3">user_id</th>
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3">Race</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-700/60 text-zinc-200">
                    @foreach ($this->streamers as $streamer)
                        <tr wire:key="streamer-{{ $streamer->id }}">
                            <td class="px-4 py-3 align-middle font-mono text-zinc-300">{{ $streamer->user_id }}</td>

                            @if ($editingId === $streamer->id)
                                {{-- Edit mode --}}
                                <td class="px-4 py-3 align-middle">
                                    <input
                                        type="text"
                                        wire:model="editLabel"
                                        wire:keydown.enter="saveEdit"
                                        wire:keydown.escape="cancelEdit"
                                        class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-sm text-zinc-100 focus:border-rose-500 focus:outline-none"
                                    >
                                    @error('editLabel')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <select
                                        wire:model="editRace"
                                        class="w-full rounded border border-zinc-700 bg-zinc-900 px-2 py-1 text-sm text-zinc-100 focus:border-rose-500 focus:outline-none"
                                    >
                                        <option value="">—</option>
                                        @foreach ($races as $label)
                                            <option value="{{ $label }}" class="bg-zinc-900 text-zinc-100">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('editRace')
                                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <button
                                        type="button"
                                        wire:click="saveEdit"
                                        class="text-xs font-semibold text-emerald-400 hover:text-emerald-300"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="cancelEdit"
                                        class="ml-3 text-xs text-zinc-400 hover:text-zinc-200"
                                    >
                                        Cancel
                                    </button>
                                </td>
                            @else
                                {{-- Read-only mode --}}
                                <td class="px-4 py-3 align-middle text-zinc-100">{{ $streamer->label }}</td>
                                <td class="px-4 py-3 align-middle">
                                    @if ($streamer->race)
                                        <span
                                            class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold uppercase text-white"
                                            style="background-color: var(--color-race-{{ $streamer->race }})"
                                        >
                                            {{ $streamer->race }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-500">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right align-middle">
                                    <button
                                        type="button"
                                        wire:click="startEdit({{ $streamer->id }})"
                                        class="text-xs text-zinc-300 hover:text-zinc-100"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $streamer->id }})"
                                        wire:confirm="Delete {{ $streamer->label }}?"
                                        class="ml-3 text-xs text-rose-400 hover:text-rose-300"
                                    >
                                        Delete
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>