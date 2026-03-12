<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\PlayerName;
use Illuminate\Database\Seeder;

class PlayerNamesSeeder extends Seeder
{
    public function run(): void
    {
        PlayerName::truncate();

        $mainPlayers = Player::whereNull('player_id')
            ->with('aliases')
            ->cursor();

        $insert = [];

        foreach ($mainPlayers as $player) {
            $insert[] = [
                'player_id'  => $player->id,
                'name'       => $player->name,
                'is_primary' => true,
            ];

            foreach ($player->aliases as $alias) {
                $insert[] = [
                    'player_id'  => $player->id,
                    'name'       => $alias->name,
                    'is_primary' => false,
                ];
            }

            if (count($insert) >= 500) {
                PlayerName::insert($insert);
                $insert = [];
            }
        }

        if (!empty($insert)) {
            PlayerName::insert($insert);
        }
    }
}