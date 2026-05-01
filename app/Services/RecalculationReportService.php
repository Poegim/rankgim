<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Player;
use App\Models\RecalculationReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecalculationReportService
{
    /**
     * Race accent colors map — references CSS custom properties from app.css.
     * Used to inline-style race tags in generated markdown HTML.
     */
    private const RACE_COLORS = [
        'Terran'  => 'var(--color-race-terran)',
        'Zerg'    => 'var(--color-race-zerg)',
        'Protoss' => 'var(--color-race-protoss)',
        'Random'  => 'var(--color-race-random)',
        'Unknown' => 'var(--color-race-unknown)',
    ];

    /**
     * Single-letter race labels used inside the inline race tag.
     */
    private const RACE_LETTERS = [
        'Terran'  => 'T',
        'Zerg'    => 'Z',
        'Protoss' => 'P',
        'Random'  => 'R',
        'Unknown' => '?',
    ];

    /**
     * Generate a recalculation report and a corresponding markdown article.
     * Must run AFTER EloService::recalculateAll().
     */
    public function generate(): RecalculationReport
    {
        $now      = now();
        $previous = $this->getPreviousRecalculatedAt();

        $summary = [
            'tournaments'  => $this->buildTournamentsDelta($previous),
            'new_players'  => $this->buildNewPlayers($previous),
            'risers'       => $this->buildRisers($previous),
            'fallers'      => $this->buildFallers($previous),
            'achievements' => $this->buildAchievements($previous),
            'totals'       => [],
        ];

        $summary['totals'] = [
            'games_added'         => collect($summary['tournaments'])->sum('games_added'),
            'tournaments_touched' => count($summary['tournaments']),
            'new_players_count'   => count($summary['new_players']),
            'achievements_count'  => count($summary['achievements']),
            'is_initial'          => $previous === null,
        ];

        $report = RecalculationReport::create([
            'recalculated_at'          => $now,
            'previous_recalculated_at' => $previous,
            'summary'                  => $summary,
        ]);

        // Only create an article if something meaningful changed since last recalc.
        // Initial baseline always gets an article so the news widget has a starting point.
        if ($this->hasMeaningfulChanges($summary)) {
            $this->createArticle($report);
        }

        return $report;

    }

    /**
     * Decide whether this report deserves a published article.
     * Initial baseline always counts. Otherwise we need at least new games or new players.
     */
    private function hasMeaningfulChanges(array $summary): bool
    {
        if ($summary['totals']['is_initial']) {
            return true;
        }

        return $summary['totals']['games_added'] > 0
            || $summary['totals']['new_players_count'] > 0
            || $summary['totals']['achievements_count'] > 0;
    }

    /**
     * List achievements unlocked since the previous recalculate.
     * Excludes "showing_up" and "rookie_mistake" — too common to be noteworthy
     * (consistent with RecentAchievements dashboard widget).
     *
     * Note: player_achievements gets truncated on every rebuild, but unlocked_at
     * is preserved (it's the actual date of unlock, not insert). So we can safely
     * filter by unlocked_at > previous to find genuinely new unlocks.
     */
    private function buildAchievements(?CarbonInterface $previous): array
    {
        if ($previous === null) {
            return [];
        }

        $excluded = ['showing_up', 'rookie_mistake'];
        $definitions = config('achievements');

        return DB::table('player_achievements as pa')
            ->join('players', 'players.id', '=', 'pa.player_id')
            ->whereNull('players.player_id')
            ->whereNotIn('pa.key', $excluded)
            ->where('pa.unlocked_at', '>', $previous)
            ->orderByDesc('pa.unlocked_at')
            ->select(
                'players.id as player_id',
                'players.name',
                'players.country_code',
                'players.race',
                'pa.key',
                'pa.tier',
                'pa.unlocked_at',
            )
            ->get()
            ->map(function ($row) use ($definitions) {
                $def = $definitions[$row->key] ?? null;
                return [
                    'player_id'    => (int) $row->player_id,
                    'name'         => $row->name,
                    'country_code' => $row->country_code,
                    'race'         => $row->race,
                    'key'          => $row->key,
                    'tier'         => $row->tier,
                    'achievement_name' => $def['name'] ?? $row->key,
                    'unlocked_at'  => $row->unlocked_at,
                ];
            })
            ->all();
    }

    /**
     * Generate a markdown article from the report and persist it.
     */
    private function createArticle(RecalculationReport $report): Article
    {
        $date = $report->recalculated_at->format('F j, Y');

        $title = $report->summary['totals']['is_initial']
            ? "Ranking baseline — {$date}"
            : "Ranking update — {$date}";

        $slug = 'ranking-update-' . $report->recalculated_at->format('Y-m-d-His');

        return Article::create([
            'type'                    => Article::TYPE_UPDATE,
            'title'                   => $title,
            'slug'                    => $slug,
            'body'                    => $this->buildMarkdown($report),
            'recalculation_report_id' => $report->id,
            'published_at'            => $report->recalculated_at,
        ]);
    }

    /**
     * Build the markdown body of the article from report summary.
     * Uses inline HTML for flags and race tags — Article model has html_input=allow.
     */
    private function buildMarkdown(RecalculationReport $report): string
    {
        $totals = $report->summary['totals'];

        if ($totals['is_initial']) {
            return "This is the first published ranking update. Future updates will show what changed since the previous one.";
        }

        $previousDate = $report->previous_recalculated_at->format('F j, Y');
        $lines        = [];

        // Intro paragraph.
        $lines[] = "Ranking recalculated. Changes since **{$previousDate}**:";
        $lines[] = '';
        $lines[] = "- **{$totals['games_added']}** new games added";
        $lines[] = "- **{$totals['tournaments_touched']}** tournaments touched";
        $lines[] = "- **{$totals['new_players_count']}** new players";
        $lines[] = '';

        // Tournaments section.
        if (count($report->summary['tournaments']) > 0) {
            $lines[] = '## Tournaments updated';
            $lines[] = '';
            foreach ($report->summary['tournaments'] as $t) {
                $link = $this->renderTournamentLink($t['tournament_id'], $t['tournament_name']);
                $lines[] = "- {$link} <span class=\"delta-positive\">+{$t['games_added']}</span> games";
            }
            $lines[] = '';
        }

        // New players section.
        if (count($report->summary['new_players']) > 0) {
            $lines[] = '## New players';
            $lines[] = '';
            $rendered = array_map(
                fn($p) => $this->renderPlayerLink($p['id'], $p['name'], $p['country_code'], $p['race']),
                $report->summary['new_players']
            );
            $lines[] = implode(', ', $rendered);
            $lines[] = '';
        }

        // Risers / fallers.
        if (count($report->summary['risers']) > 0) {
            $lines[] = '## Biggest gainers from new games';
            $lines[] = '';
            foreach ($report->summary['risers'] as $p) {
                $link = $this->renderPlayerLink($p['id'], $p['name'], $p['country_code'], $p['race']);
                $lines[] = "- {$link} <span class=\"delta-positive\">+{$p['rating_change']}</span>";
            }
            $lines[] = '';
        }

        if (count($report->summary['fallers']) > 0) {
            $lines[] = '## Biggest losers on new games';
            $lines[] = '';
            foreach ($report->summary['fallers'] as $p) {
                $link = $this->renderPlayerLink($p['id'], $p['name'], $p['country_code'], $p['race']);
                $lines[] = "- {$link} <span class=\"delta-negative\">{$p['rating_change']}</span>";
            }
            $lines[] = '';
        }

        // Achievements section.
        if (count($report->summary['achievements']) > 0) {
            $lines[] = '## Achievements unlocked';
            $lines[] = '';
            foreach ($report->summary['achievements'] as $a) {
                $playerLink = $this->renderPlayerLink($a['player_id'], $a['name'], $a['country_code'], $a['race']);
                $tierTag    = $this->renderTierTag($a['tier']);
                $lines[] = "- {$playerLink} unlocked {$tierTag} **{$a['achievement_name']}**";
            }
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * Render an achievement tier badge as inline HTML.
     * Colors come from --color-rank-* variables in app.css.
     */
    private function renderTierTag(string $tier): string
    {
        $tier = strtolower($tier);

        // Map tier to one of the rank CSS variables for color.
        $colorMap = [
            's' => 'var(--color-rank-olympic)',
            'a' => 'var(--color-rank-a)',
            'b' => 'var(--color-rank-b)',
            'c' => 'var(--color-rank-c)',
            'd' => 'var(--color-rank-d)',
        ];

        $color = $colorMap[$tier] ?? 'var(--color-zinc-500)';
        $letter = strtoupper($tier);

        return "<span class=\"tier-badge\" "
             . "style=\"background: color-mix(in srgb, {$color} 25%, transparent); color: {$color};\">"
             . "{$letter}"
             . "</span>";
    }


    /**
     * Render a player as a compact "pill" — flag strip + race tint + nickname.
     * Same visual pattern as event-card and dashboard player chips, so users
     * recognize it instantly. Self-contained styling — does not rely on `prose`.
     */
    private function renderPlayerLink(int $id, string $name, ?string $countryCode, ?string $race): string
    {
        $url = route('players.show', ['id' => $id, 'slug' => Str::slug($name)]);

        $race  = $race ?: 'Unknown';
        $color = self::RACE_COLORS[$race] ?? self::RACE_COLORS['Unknown'];
        $letter = self::RACE_LETTERS[$race] ?? '?';
        $safeName = e($name);

        // Flag strip — left side of the pill, fixed width with bg-cover.
        $flag = '';
        if ($countryCode) {
            $flagSrc = asset('images/country_flags/' . strtolower($countryCode) . '.svg');
            $flag = "<span class=\"player-pill__flag\" style=\"background-image: url('{$flagSrc}');\" aria-label=\"{$countryCode}\"></span>";
        }

        // Race tag — middle, tinted background + colored letter.
        $raceTag = "<span class=\"player-pill__race\" "
                 . "style=\"background: color-mix(in srgb, {$color} 20%, transparent); color: {$color};\">{$letter}</span>";

        return "<a href=\"{$url}\" class=\"player-pill\" wire:navigate>"
             . "{$flag}{$raceTag}<span class=\"player-pill__name\">{$safeName}</span>"
             . "</a>";
    }

    private function renderTournamentLink(int $id, string $name): string
    {
        $url = route('tournaments.show', $id);
        $safeName = e($name);

        return "<a href=\"{$url}\" class=\"tournament-link\" wire:navigate>"
             . "<span class=\"tournament-link__icon\">🏆</span>"
             . "<span class=\"tournament-link__name\">{$safeName}</span>"
             . "</a>";
    }

    private function getPreviousRecalculatedAt(): ?CarbonInterface
    {
        $previous = RecalculationReport::orderByDesc('recalculated_at')
            ->where('id', '!=', optional(RecalculationReport::orderByDesc('id')->first())->id ?? 0)
            ->first();
        return $previous?->recalculated_at;
    }

    private function buildTournamentsDelta(?CarbonInterface $previous): array
    {
        if ($previous === null) {
            return [];
        }

        return DB::table('games')
            ->join('tournaments', 'tournaments.id', '=', 'games.tournament_id')
            ->where('games.created_at', '>', $previous)
            ->selectRaw('
                tournaments.id as tournament_id,
                tournaments.name as tournament_name,
                COUNT(*) as games_added
            ')
            ->groupBy('tournaments.id', 'tournaments.name')
            ->orderByDesc('games_added')
            ->get()
            ->map(fn($row) => [
                'tournament_id'   => (int) $row->tournament_id,
                'tournament_name' => $row->tournament_name,
                'games_added'     => (int) $row->games_added,
            ])
            ->all();
    }

    private function buildNewPlayers(?CarbonInterface $previous): array
    {
        if ($previous === null) {
            return [];
        }

        return Player::query()
            ->whereNull('player_id')
            ->where('created_at', '>', $previous)
            ->orderBy('created_at')
            ->get(['id', 'name', 'country_code', 'race'])
            ->map(fn($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'country_code' => $p->country_code,
                'race'         => $p->race,
            ])
            ->all();
    }

    private function buildRisers(?CarbonInterface $previous): array
    {
        if ($previous === null) {
            return [];
        }

        return $this->buildRatingChange($previous, 'desc');
    }

    private function buildFallers(?CarbonInterface $previous): array
    {
        if ($previous === null) {
            return [];
        }

        return $this->buildRatingChange($previous, 'asc');
    }

    private function buildRatingChange(CarbonInterface $previous, string $direction): array
    {
        return DB::table('rating_histories as rh')
            ->join('games', 'games.id', '=', 'rh.game_id')
            ->join('players', 'players.id', '=', 'rh.player_id')
            ->join('player_ratings', 'player_ratings.player_id', '=', 'rh.player_id')
            ->where('games.created_at', '>', $previous)
            ->whereNull('players.player_id')
            ->selectRaw('
                players.id,
                players.name,
                players.country_code,
                players.race,
                player_ratings.rating as current_rating,
                SUM(rh.rating_change) as rating_change
            ')
            ->groupBy('players.id', 'players.name', 'players.country_code', 'players.race', 'player_ratings.rating')
            ->orderByRaw('rating_change ' . ($direction === 'desc' ? 'DESC' : 'ASC'))
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'id'             => (int) $row->id,
                'name'           => $row->name,
                'country_code'   => $row->country_code,
                'race'           => $row->race,
                'current_rating' => (int) $row->current_rating,
                'rating_change'  => (int) $row->rating_change,
            ])
            ->all();
    }
}