<?php

namespace App\Livewire\Games;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\EloService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Create extends Component
{
    public int $tournamentId;

    public string $winnerSearch = '';
    public ?int $winnerId = null;
    public string $winnerName = '';

    public string $loserSearch = '';
    public ?int $loserId = null;
    public string $loserName = '';

    public string $dateTime = '';
    public int $result = 1;

    #[Computed]
    public function tournament()
    {
        return Tournament::findOrFail($this->tournamentId);
    }

    #[Computed]
    public function winnerResults()
    {
        if (strlen($this->winnerSearch) < 2) return collect();
        return $this->searchPlayers($this->winnerSearch);
    }

    #[Computed]
    public function loserResults()
    {
        if (strlen($this->loserSearch) < 2) return collect();
        return $this->searchPlayers($this->loserSearch);
    }

    private function searchPlayers(string $search)
    {
        return Player::query()
            ->select('players.*')
            ->whereNull('players.player_id')
            ->where(function ($q) use ($search) {
                $q->where('players.name', 'like', '%' . $search . '%')
                  ->orWhereExists(function ($sub) use ($search) {
                      $sub->from('players as aliases')
                          ->whereColumn('aliases.player_id', 'players.id')
                          ->where('aliases.name', 'like', '%' . $search . '%');
                  });
            })
            ->with('aliases')
            ->orderBy('players.name')
            ->limit(8)
            ->get();
    }

    public function selectWinner(int $id, string $name): void
    {
        $this->winnerId = $id;
        $this->winnerName = $name;
        $this->winnerSearch = $name;
    }

    public function selectLoser(int $id, string $name): void
    {
        $this->loserId = $id;
        $this->loserName = $name;
        $this->loserSearch = $name;
    }

    public function save(): void
    {
        $this->validate([
            'winnerId' => 'required|exists:players,id|different:loserId',
            'loserId'  => 'required|exists:players,id',
            'dateTime' => 'required|date',
            'result'   => 'required|in:1,3',
        ]);

        $game = Game::create([
            'winner_id'     => $this->winnerId,
            'loser_id'      => $this->loserId,
            'tournament_id' => $this->tournamentId,
            'date_time'     => $this->dateTime,
            'result'        => $this->result,
        ]);

        app(EloService::class)->processGame($game);

        session()->flash('success', 'Game added successfully!');
        $this->redirect(route('tournaments.show', $this->tournamentId), navigate: true);
    }

    public function render()
    {
        return view('livewire.games.create');
    }
}