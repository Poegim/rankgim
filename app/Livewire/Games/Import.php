<?php
namespace App\Livewire\Games;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tournament;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Import extends Component
{
    public int $tournamentId;
    public string $rawInput = '';
    public string $dateTime = '';
    public array $parsed = [];
    public bool $isParsed = false;
    public ?int $editingIndex = null;
    public ?string $editingSide = null;
    public string $modalSearch = '';
    public bool $modalOpen = false;

    public function mount(): void
    {
        $this->dateTime = now()->format('Y-m-d\TH:i');
    }

    #[Computed]
    public function tournament()
    {
        return Tournament::findOrFail($this->tournamentId);
    }

    public function modalResults()
    {
        if (strlen($this->modalSearch) < 2) return collect();
        return $this->searchPlayers($this->modalSearch);
    }

    private function searchPlayers(string $search)
    {
        $playerIds = \App\Models\PlayerName::where('name', 'like', '%' . $search . '%')
            ->pluck('player_id');

        return Player::whereIn('id', $playerIds)
            ->whereNull('player_id')
            ->with('aliases')
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function openModal(int $index, string $side): void
    {
        $this->editingIndex = $index;
        $this->editingSide = $side;
        $this->modalSearch = '';
        $this->resetErrorBag();
        $this->modalOpen = true;
    }

    public function selectPlayer(int $playerId): void
    {
        if ($this->editingIndex === null || $this->editingSide === null) return;

        $player = Player::find($playerId);
        if (!$player) return;

        $index = $this->editingIndex;
        $side = $this->editingSide;

        if ($side === 'winner' && $this->parsed[$index]['loser_id'] == $playerId) {
            $this->addError('modalSearch', 'Winner and loser must be different');
            return;
        }
        if ($side === 'loser' && $this->parsed[$index]['winner_id'] == $playerId) {
            $this->addError('modalSearch', 'Winner and loser must be different');
            return;
        }
        $this->parsed[$index][$side . '_id'] = $playerId;
        $this->parsed[$index][$side] = ['id' => $player->id, 'name' => $player->name, 'country_code' => $player->country_code];
        $this->parsed[$index]['status'] =
            ($this->parsed[$index]['winner_id'] && $this->parsed[$index]['loser_id']) ? 'ok' : 'unmatched';

        $this->editingIndex = null;
        $this->editingSide = null;
        $this->modalSearch = '';
        $this->modalOpen = false;
    }

    public function parse(): void
    {
        $lines = array_filter(array_map('trim', explode("\n", $this->rawInput)), fn($line) => $line !== '');
        
        $results = [];
        $currentDate = null;

        foreach ($lines as $line) {
            // Sprawdź czy linia to data
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $line)) {
                $currentDate = trim($line);
                continue;
            }

            $parts = array_map('trim', explode(',', $line));
            if (count($parts) !== 2) {
                $results[] = ['raw' => $line, 'error' => 'Invalid format', 'winner' => null, 'loser' => null, 'status' => 'error', 'date' => $currentDate];
                continue;
            }

            [$winnerName, $loserName] = $parts;
            $winner = $this->findPlayer($winnerName);
            $loser = $this->findPlayer($loserName);

            $results[] = [
                'raw' => $line,
                'winner_name' => $winnerName,
                'loser_name' => $loserName,
                'winner' => $winner ? ['id' => $winner->id, 'name' => $winner->name, 'country_code' => $winner->country_code] : null,
                'loser' => $loser ? ['id' => $loser->id, 'name' => $loser->name, 'country_code' => $loser->country_code] : null,
                'winner_id' => $winner?->id,
                'loser_id' => $loser?->id,
                'date' => $currentDate,
                'status' => ($winner && $loser) ? 'ok' : 'unmatched',
            ];
        }

        $this->parsed = $results;
        $this->isParsed = true;
    }

    private function findPlayer(string $name): ?Player
    {
        $player = Player::whereNull('player_id')->where('name', $name)->first();
        if ($player) return $player;
        $alias = Player::whereNotNull('player_id')->where('name', $name)->with('aka')->first();
        if ($alias) return $alias->aka;
        $player = Player::whereNull('player_id')->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($player) return $player;
        $alias = Player::whereNotNull('player_id')->whereRaw('LOWER(name) = ?', [strtolower($name)])->with('aka')->first();
        return $alias?->aka;
    }

    public function removeRow(int $index): void
    {
        array_splice($this->parsed, $index, 1);
    }

    public function save(): void
    {
        $ready = collect($this->parsed)->filter(fn($r) => $r['status'] === 'ok');
        foreach ($ready as $row) {
            Game::create([
                'tournament_id' => $this->tournamentId,
                'winner_id' => $row['winner_id'],
                'loser_id' => $row['loser_id'],
                'date_time' => $row['date'] ?? $this->dateTime,
                'result' => 1,
            ]);
        }
        session()->flash('success', $ready->count() . ' games imported!');
        $this->redirect(route('tournaments.show', $this->tournamentId), navigate: true);
    }

    public function render()
    {
        return view('livewire.games.import');
    }
}