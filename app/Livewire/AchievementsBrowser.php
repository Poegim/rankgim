<?php

namespace App\Livewire;

use App\Models\PlayerAchievement;
use App\Models\PlayerRating;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class AchievementsBrowser extends Component
{
    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterTier = '';

    #[Url]
    public string $sortBy = 'category'; // category | popularity | tier

    // Key of the achievement whose holders modal is open
    public ?string $holdersKey = null;

    #[Computed]
    public function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    #[Computed]
    public function totalPlayers(): int
    {
        return PlayerRating::whereHas(
            'player', fn($q) => $q->whereNull('player_id')
        )->count();
    }

    /**
     * All achievement definitions enriched with owners_count from the DB.
     * Secrets are masked for non-admins.
     */
    #[Computed]
    public function achievements(): \Illuminate\Support\Collection
    {
        $definitions  = collect(config('achievements'));
        $isAdmin      = $this->isAdmin;
        $totalPlayers = $this->totalPlayers;

        // Load owners_count for every key in a single query
        $ownerCounts = PlayerAchievement::selectRaw('`key`, MAX(owners_count) as owners_count')
            ->groupBy('key')
            ->pluck('owners_count', 'key');

        $items = $definitions->map(function ($def, $key) use ($ownerCounts, $isAdmin, $totalPlayers) {
            $ownersCount = $ownerCounts->get($key, 0);
            $pct         = $totalPlayers > 0 ? round(($ownersCount / $totalPlayers) * 100, 2) : 0;

            // Mask secret achievements for non-admins
            if ($def['secret'] && !$isAdmin) {
                return [
                    'key'          => $key,
                    'name'         => '???',
                    'description'  => null,
                    'tier'         => $def['tier'],
                    'category'     => $def['category'],
                    'secret'       => true,
                    'group'        => $def['group'] ?? null,
                    'owners_count' => $ownersCount,
                    'pct'          => $pct,
                    'masked'       => true,
                ];
            }

            return [
                'key'          => $key,
                'name'         => $def['name'],
                'description'  => $def['description'],
                'tier'         => $def['tier'],
                'category'     => $def['category'],
                'secret'       => $def['secret'],
                'group'        => $def['group'] ?? null,
                'owners_count' => $ownersCount,
                'pct'          => $pct,
                'masked'       => false,
            ];
        });

        // Apply category filter
        if ($this->filterCategory !== '') {
            $items = $items->filter(fn($a) => $a['category'] === $this->filterCategory);
        }

        // Apply tier filter
        if ($this->filterTier !== '') {
            $items = $items->filter(fn($a) => $a['tier'] === $this->filterTier);
        }

        // Sort
        return match ($this->sortBy) {
            'popularity' => $items->sortByDesc('owners_count')->values(),
            'tier'       => $items->sortBy(fn($a) => array_search($a['tier'], ['s', 'a', 'b', 'c', 'd']))->values(),
            default      => $items->sortBy(['category', 'tier'])->values(),
        };
    }

    /**
     * Players who hold the achievement identified by $holdersKey.
     */
    #[Computed]
    public function holders(): \Illuminate\Support\Collection
    {
        if (!$this->holdersKey) {
            return collect();
        }

        return PlayerAchievement::where('key', $this->holdersKey)
            ->with('player')
            ->orderBy('unlocked_at')
            ->get()
            ->map(fn($pa) => [
                'name'        => $pa->player->name,
                'id'          => $pa->player->id,
                'slug'        => \Illuminate\Support\Str::slug($pa->player->name),
                'country_code'=> $pa->player->country_code,
                'unlocked_at' => $pa->unlocked_at,
            ]);
    }

    public function openHolders(string $key): void
    {
        $this->holdersKey = $key;
    }

    public function closeHolders(): void
    {
        $this->holdersKey = null;
        unset($this->holders); // clear computed cache
    }

    public function render()
    {
        return view('livewire.achievements-browser')
            ->title('Achievements');
    }
}