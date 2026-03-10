<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public function updateRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        
        if ($user->id === auth()->id()) return;

        $user->update(['role' => $role]);
        
        $this->dispatch('role-updated');
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.users');
    }
}