<?php

namespace App\Livewire\Admin;

use App\Services\EloService;
use Livewire\Component;

class Recalculate extends Component
{
    public bool $processing = false;
    public ?string $lastRun = null;

    public function recalculate(): void
    {
        $this->processing = true;
        app(EloService::class)->recalculateAll();
        $this->processing = false;
        $this->lastRun = now()->format('Y-m-d H:i:s');
        $this->dispatch('recalculated');
    }

    public function render()
    {
        return view('livewire.admin.recalculate');
    }
}