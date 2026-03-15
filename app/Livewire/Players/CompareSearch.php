<?php

namespace App\Livewire\Players;

use App\Models\Player;
use App\Models\PlayerName;
use Livewire\Component;

class CompareSearch extends Component
{
    public string $search1 = '';
    public string $search2 = '';

    public ?int $player1Id = null;
    public ?int $player2Id = null;
    public string $player1Name = '';
    public string $player2Name = '';

    public bool $open1 = false;
    public bool $open2 = false;

    public function getResults1Property()
    {
        if (strlen($this->search1) < 2) return collect();

        $ids = PlayerName::where('name', 'like', '%' . $this->search1 . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $ids)
            ->whereNull('player_id')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function getResults2Property()
    {
        if (strlen($this->search2) < 2) return collect();

        $ids = PlayerName::where('name', 'like', '%' . $this->search2 . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $ids)
            ->whereNull('player_id')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function selectPlayer1(int $id, string $name): void
    {
        $this->player1Id = $id;
        $this->player1Name = $name;
        $this->search1 = $name;
        $this->open1 = false;
    }

    public function selectPlayer2(int $id, string $name): void
    {
        $this->player2Id = $id;
        $this->player2Name = $name;
        $this->search2 = $name;
        $this->open2 = false;
    }

    public function clearPlayer1(): void
    {
        $this->player1Id = null;
        $this->player1Name = '';
        $this->search1 = '';
    }

    public function clearPlayer2(): void
    {
        $this->player2Id = null;
        $this->player2Name = '';
        $this->search2 = '';
    }

    public function compare(): void
    {
        if (!$this->player1Id || !$this->player2Id) return;
        if ($this->player1Id === $this->player2Id) return;

        $this->redirectRoute('players.compare', [
            'id1' => $this->player1Id,
            'id2' => $this->player2Id,
        ]);
    }

    public function render()
    {
        return view('livewire.players.compare-search');
    }
}