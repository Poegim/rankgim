<?php

namespace App\Livewire\Tournaments;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public int $tournamentId;
    
    // Modal states
    public bool $showAddModal = false;
    public bool $showEditModal = false;
    
    // Form fields
    public $winnerId = '';
    public $loserId = '';
    public $dateTime = '';
    public $result = '1';
    
    // Edit
    public $editGameId = null;
    public $editWinnerId = '';
    public $editLoserId = '';
    public $editDateTime = '';
    public $editResult = '';
    
    // Player search
    public string $winnerSearch = '';
    public string $loserSearch = '';
    public string $editWinnerSearch = '';
    public string $editLoserSearch = '';

    public function mount(int $tournamentId)
    {
        $this->tournamentId = $tournamentId;
        $this->dateTime = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function tournament()
    {
        return Tournament::findOrFail($this->tournamentId);
    }

    #[Computed]
    public function games()
    {
        return Game::where('tournament_id', $this->tournamentId)
            ->with(['winner', 'loser'])
            ->orderByDesc('date_time')
            ->orderByDesc('id')
            ->paginate(20);
    }

    public function updated($property)
    {
        if ($property === 'loserId' || $property === 'editLoserId') {
            $this->validateOnly($property);
        }
    }

    public function getWinnerResultsProperty()
    {
        if (strlen($this->winnerSearch) < 2) {
            return collect();
        }

        return Player::where('name', 'like', '%' . $this->winnerSearch . '%')
            ->whereNull('player_id')
            ->limit(8)
            ->get();
    }

    public function getLoserResultsProperty()
    {
        if (strlen($this->loserSearch) < 2) {
            return collect();
        }

        return Player::where('name', 'like', '%' . $this->loserSearch . '%')
            ->whereNull('player_id')
            ->limit(8)
            ->get();
    }

    public function getEditWinnerResultsProperty()
    {
        if (strlen($this->editWinnerSearch) < 2) {
            return collect();
        }

        return Player::where('name', 'like', '%' . $this->editWinnerSearch . '%')
            ->whereNull('player_id')
            ->limit(8)
            ->get();
    }

    public function getEditLoserResultsProperty()
    {
        if (strlen($this->editLoserSearch) < 2) {
            return collect();
        }

        return Player::where('name', 'like', '%' . $this->editLoserSearch . '%')
            ->whereNull('player_id')
            ->limit(8)
            ->get();
    }

    public function openAddModal()
    {
        $this->reset(['winnerId', 'loserId', 'winnerSearch', 'loserSearch', 'result']);
        $this->dateTime = now()->format('Y-m-d\TH:i');
        $this->result = '1';
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['winnerId', 'loserId', 'winnerSearch', 'loserSearch']);
    }

    public function selectWinner($playerId, $playerName)
    {
        if ($playerId == $this->loserId) {
            $this->addError('winnerId', 'Winner and loser must be different players');
            return;
        }

        $this->resetErrorBag('winnerId');
        $this->winnerId = $playerId;
        $this->winnerSearch = $playerName;
    }

    public function selectLoser($playerId, $playerName)
    {
        if ($playerId == $this->winnerId) {
            $this->addError('loserId', 'Winner and loser must be different players');
            return;
        }

        $this->resetErrorBag('loserId');
        $this->loserId = $playerId;
        $this->loserSearch = $playerName;
    }

    public function selectEditWinner($playerId, $playerName)
    {
        $this->editWinnerId = $playerId;
        $this->editWinnerSearch = $playerName;
    }

    public function selectEditLoser($playerId, $playerName)
    {
        $this->editLoserId = $playerId;
        $this->editLoserSearch = $playerName;
    }

    public function save()
    {
        $this->validate([
            'winnerId' => 'required|exists:players,id',
            'loserId' => 'required|exists:players,id|different:winnerId',
            'dateTime' => 'required|date',
            'result' => 'required|in:1,3',
        ], [
            'winnerId.required' => 'Please select a winner',
            'loserId.required' => 'Please select a loser',
            'loserId.different' => 'Winner and loser must be different players',
        ]);

        Game::create([
                'tournament_id' => $this->tournamentId,
                'winner_id' => $this->winnerId,
                'loser_id' => $this->loserId,
                'date_time' => $this->dateTime,
                'result' => $this->result,
                'user_id' => auth()->id(),
            ]);

        $this->dispatch('game-saved');
        // Don't reset dateTime - keep it for next game
        $this->reset(['winnerId', 'loserId', 'winnerSearch', 'loserSearch']);
        $this->showAddModal = false;
    }

    public function edit($gameId)
    {
        $game = Game::with(['winner', 'loser'])->findOrFail($gameId);
        
        $this->editGameId = $game->id;
        $this->editWinnerId = $game->winner_id;
        $this->editLoserId = $game->loser_id;
        $this->editWinnerSearch = $game->winner->name;
        $this->editLoserSearch = $game->loser->name;
        $this->editDateTime = \Carbon\Carbon::parse($game->date_time)->format('Y-m-d\TH:i');
        $this->editResult = (string) $game->result;
        
        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate([
            'editWinnerId' => 'required|exists:players,id',
            'editLoserId' => 'required|exists:players,id|different:editWinnerId',
            'editDateTime' => 'required|date',
            'editResult' => 'required|in:1,3',
        ], [
            'editWinnerId.required' => 'Please select a winner',
            'editLoserId.required' => 'Please select a loser',
            'editLoserId.different' => 'Winner and loser must be different players',
        ]);

        $game = Game::findOrFail($this->editGameId);
        
        $game->update([
            'winner_id' => $this->editWinnerId,
            'loser_id' => $this->editLoserId,
            'date_time' => $this->editDateTime,
            'result' => $this->editResult,
        ]);

        $this->showEditModal = false;
        $this->dispatch('game-updated');
        $this->reset(['editGameId', 'editWinnerId', 'editLoserId', 'editWinnerSearch', 'editLoserSearch']);
    }

    public function delete($gameId)
    {
        Game::findOrFail($gameId)->delete();
        
        $this->dispatch('game-deleted');
    }

    public function render()
    {
        return view('livewire.tournaments.show');
    }
}