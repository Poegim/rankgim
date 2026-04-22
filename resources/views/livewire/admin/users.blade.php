<div>
    <div
        x-data="{ show: false }"
        x-on:role-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Role updated
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">User Management</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ $this->users->total() }} users</p>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Joined</flux:table.column>
                <flux:table.column>Comments</flux:table.column>
                <flux:table.column>Reactions</flux:table.column>

            </flux:table.columns>
            <flux:table.rows>
                @foreach($this->users as $user)
                <flux:table.row :key="$user->id" class="[&>td]:py-2">
                    <flux:table.cell>
                        <span class="font-semibold text-[0.9375rem] text-zinc-800 dark:text-white">{{ $user->name }}</span>
                        @if($user->id === auth()->id())
                            <span class="ml-2 text-xs text-zinc-400">(you)</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $user->email }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($user->id === auth()->id())
                            <flux:badge color="red">{{ $user->role }}</flux:badge>
                        @else
                            <flux:select wire:change="updateRole({{ $user->id }}, $event.target.value)" class="text-sm">
                                <option value="user"    {{ $user->role === 'user'  ? 'selected' : '' }}>User</option>
                                <option value="mod"     {{ $user->role === 'mod'   ? 'selected' : '' }}>Moderator</option>
                                <option value="admin"   {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </flux:select>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-xs text-zinc-400">{{ $user->created_at->format('Y-m-d') }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-sm text-zinc-500">{{ $user->comments->count() }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-sm text-zinc-500">{{ $user->reactions->count() }}</span>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
</div>