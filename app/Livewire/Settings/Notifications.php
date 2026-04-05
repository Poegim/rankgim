<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Notifications')]
class Notifications extends Component
{
    public bool $eventReminders;

    public function mount(): void
    {
        $this->eventReminders = auth()->user()->event_reminders;
    }

    public function updateEventReminders(): void
    {
        auth()->user()->update(['event_reminders' => $this->eventReminders]);
    }

    public function render()
    {
        return view('livewire.settings.notifications');
    }
}