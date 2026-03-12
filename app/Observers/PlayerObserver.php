<?php

namespace App\Observers;

use App\Models\Player;
use App\Models\PlayerName;

class PlayerObserver
{
    public function saved(Player $player): void
    {
        // Jeśli player_id się zmieniło, zsynchronizuj też starego głównego
        if ($player->wasChanged('player_id')) {
            $oldMainId = $player->getOriginal('player_id');
            if ($oldMainId) {
                $oldMain = Player::find($oldMainId);
                if ($oldMain) $this->sync($oldMain);
            }
        }

        $this->sync($player);
    }

    public function deleted(Player $player): void
    {
        // cascadeOnDelete w migracji ogarnie player_names dla głównego gracza
        // ale jeśli usuwamy alias, trzeba odświeżyć głównego gracza
        if ($player->player_id) {
            $main = Player::find($player->player_id);
            if ($main) $this->sync($main);
        }
    }

    private function sync(Player $player): void
    {
        // Jeśli to alias — sync głównego gracza
        $mainPlayer = $player->player_id
            ? Player::find($player->player_id)
            : $player;

        if (!$mainPlayer) return;

        // Pobierz wszystkie nazwy: główny + aliasy
        $aliases = Player::where('player_id', $mainPlayer->id)->pluck('name');

        $names = collect([$mainPlayer->name])
            ->merge($aliases)
            ->unique()
            ->values();

        // Usuń stare i wstaw nowe
        PlayerName::where('player_id', $mainPlayer->id)->delete();

        PlayerName::insert(
            $names->map(fn($name, $i) => [
                'player_id'  => $mainPlayer->id,
                'name'       => $name,
                'is_primary' => $i === 0,
            ])->toArray()
        );
    }
}