<?php

namespace App\Livewire\Admin;

use App\Models\SoopStreamer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SoopStreamers extends Component
{

    public $races = SoopStreamer::RACES;

    // Form fields for the inline "add new" row.
    #[Validate('required|string|max:100|unique:soop_streamers,user_id')]
    public string $newUserId = '';

    #[Validate('required|string|max:100')]
    public string $newLabel = '';

    #[Validate('nullable|in:,zerg,protoss,terran,random')]
    public ?string $newRace = null;



    // Editing state — only one row can be edited at a time.
    public ?int $editingId = null;

    #[Validate('required|string|max:100')]
    public string $editLabel = '';

    #[Validate('nullable|in:,zerg,protoss,terran,random')]
    public ?string $editRace = null;

    #[Computed]
    public function streamers()
    {
        return SoopStreamer::query()
            ->orderBy('id', 'desc')
            ->get();
    }

    public function add(): void
    {
        $data = $this->validate([
            'newUserId' => 'required|string|max:100|unique:soop_streamers,user_id',
            'newLabel'  => 'required|string|max:100',
            'newRace'   => 'nullable|in:,zerg,protoss,terran,random',
        ]);

        SoopStreamer::create([
            'user_id' => $data['newUserId'],
            'label'   => $data['newLabel'],
            'race'    => $data['newRace'] ?: null,
        ]);

        $this->reset(['newUserId', 'newLabel', 'newRace']);
        $this->dispatch('streamer-added');
    }

    public function startEdit(int $id): void
    {
        $streamer = SoopStreamer::findOrFail($id);

        $this->editingId = $id;
        $this->editLabel = $streamer->label;
        $this->editRace  = $streamer->race;

        // Clear any leftover validation errors from a previous edit attempt.
        $this->resetErrorBag(['editLabel', 'editRace']);
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'editLabel', 'editRace']);
        $this->resetErrorBag(['editLabel', 'editRace']);
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'editLabel' => 'required|string|max:100',
            'editRace'  => 'nullable|in:,zerg,protoss,terran,random',
        ]);

        SoopStreamer::whereKey($this->editingId)->update([
            'label' => $data['editLabel'],
            'race'  => $data['editRace'] ?: null,  // empty string → null
        ]);

        $this->cancelEdit();
        $this->dispatch('streamer-updated');
    }

    public function delete(int $id): void
    {
        // If the deleted row was being edited, exit edit mode first.
        if ($this->editingId === $id) {
            $this->cancelEdit();
        }

        SoopStreamer::whereKey($id)->delete();
        $this->dispatch('streamer-deleted');
    }

    public function render()
    {
        return view('livewire.admin.soop-streamers');
    }
}