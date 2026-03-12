<?php

namespace App\Livewire\Players;

use App\Models\Player;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Modal states
    public bool $showAddModal = false;
    public bool $showEditModal = false;

    // Form fields
    public $name = '';
    public $country = '';
    public $countryCode = '';
    public $race = 'Unknown';
    public $playerId = null; // AKA

    // Edit
    public $editPlayerId = null;
    public $editName = '';
    public $editCountry = '';
    public $editCountryCode = '';
    public $editRace = '';
    public $editAkaId = null;

    // Search
    public string $search = '';
    public string $akaSearch = '';
    public string $editAkaSearch = '';

    public array $countriesList = [];

    public function mount(): void
    {
        $this->countriesList = collect(config('countries'))->values()->all();
    }

    #[Computed]
    public function akaResults()
    {
        if (strlen($this->akaSearch) < 2) {
            return collect();
        }

        $playerIds = \App\Models\PlayerName::where('name', 'like', '%' . $this->akaSearch . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id')
            ->limit(8)
            ->get();
    }

    public function getEditAkaResultsProperty()
    {
        if (strlen($this->editAkaSearch) < 2) {
            return collect();
        }

        $playerIds = \App\Models\PlayerName::where('name', 'like', '%' . $this->editAkaSearch . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id')
            ->where('id', '!=', $this->editPlayerId)
            ->limit(8)
            ->get();
    }

    public function clearEditAka()
    {
        $this->editAkaId = null;
        $this->editAkaSearch = '';
    }

    public function openAddModal()
    {
        $this->reset(['name', 'country', 'countryCode', 'race', 'playerId', 'akaSearch']);
        $this->race = 'Unknown';
        $this->resetValidation();
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->reset(['name', 'country', 'countryCode', 'race', 'playerId', 'akaSearch']);
        $this->resetValidation();
    }

    public function selectAka($playerId, $playerName)
    {
        $this->playerId = $playerId;
        $this->akaSearch = $playerName;
    }

    public function selectEditAka($playerId, $playerName)
    {
        $this->editAkaId = $playerId;
        $this->editAkaSearch = $playerName;
    }

    public function save()
    {
        $this->validate([
            'name'     => 'required|string|max:255',
            'country'  => 'required|string|size:2',
            'race'     => 'required|in:Terran,Zerg,Protoss,Random,Unknown',
            'playerId' => 'nullable|exists:players,id',
        ]);

        $countryData = collect(config('countries'))->firstWhere('code', strtoupper($this->country));

        Player::create([
            'name'         => $this->name,
            'country'      => $countryData['name'] ?? '',
            'country_code' => strtoupper($this->country),
            'race'         => $this->race,
            'player_id'    => $this->playerId,
        ]);

        $this->closeAddModal();
        $this->dispatch('player-saved');
    }

    public function edit($playerId)
    {
        $player = Player::with('aka')->findOrFail($playerId);
        $this->editPlayerId = $player->id;
        $this->editName = $player->name;
        $this->editCountry = $player->country_code; // teraz kod zamiast nazwy
        $this->editRace = $player->race;
        $this->editAkaId = $player->player_id;
        $this->editAkaSearch = $player->aka?->name ?? '';
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function update()
    {
        $this->validate([
            'editName'    => 'required|string|max:255',
            'editCountry' => 'required|string|size:2',
            'editRace'    => 'required|in:Terran,Zerg,Protoss,Random,Unknown',
            'editAkaId'   => 'nullable|exists:players,id',
        ]);

        $countryData = collect(config('countries'))->firstWhere('code', strtoupper($this->editCountry));

        $player = Player::findOrFail($this->editPlayerId);
        $player->update([
            'name'         => $this->editName,
            'country'      => $countryData['name'] ?? '',
            'country_code' => strtoupper($this->editCountry),
            'race'         => $this->editRace,
            'player_id'    => $this->editAkaId,
        ]);

        $this->showEditModal = false;
        $this->dispatch('player-updated');
    }

    public function delete($playerId)
    {
        $player = Player::withCount('gamesAsWinner', 'gamesAsLoser')->findOrFail($playerId);

        if ($player->games_as_winner_count > 0 || $player->games_as_loser_count > 0) {
            $this->dispatch('cannot-delete');
            return;
        }

        $player->delete();
        $this->dispatch('player-deleted');
    }

    public function render()
    {
        
        $search = $this->search;

        $players = Player::query()
            ->select('players.*')
            ->whereNull('players.player_id')
                ->when($search, function ($query) use ($search) {
                    $playerIds = \App\Models\PlayerName::where('name', 'like', '%' . $search . '%')
                        ->pluck('player_id');

                    $query->whereIn('players.id', $playerIds);
                })
            ->with(['aliases', 'aka'])
            ->orderBy('players.name')
            ->paginate(20);

         $canManage = auth()->check() && auth()->user()->canManageGames();

        return view('livewire.players.index', compact([
            'players',
            'canManage',
        ]));
    }
}