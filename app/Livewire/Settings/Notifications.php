<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Notifications')]
class Notifications extends Component
{
    public bool $eventReminders;
    public bool $eventRemindersStream;
    public bool $eventRemindersOpen;

    public function mount(): void
    {
        $this->eventReminders       = auth()->user()->event_reminders;
        $this->eventRemindersStream = auth()->user()->event_reminders_stream;
        $this->eventRemindersOpen   = auth()->user()->event_reminders_open;
    }

    public function updateEventRemindersStream(): void
    {
        auth()->user()->update(['event_reminders_stream' => $this->eventRemindersStream]);
    }

    public function updateEventRemindersOpen(): void
    {
        auth()->user()->update(['event_reminders_open' => $this->eventRemindersOpen]);
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