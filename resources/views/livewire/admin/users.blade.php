<div>
    {{-- ── Toast: role updated ─────────────────────────────────────────── --}}
    <div
        x-data="{ show: false }"
        x-on:role-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ✅ Role updated
    </div>

    {{-- ── Toast: balance updated ──────────────────────────────────────── --}}
    <div
        x-data="{ show: false }"
        x-on:balance-updated.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition
        class="fixed bottom-16 right-4 bg-amber-500 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-lg z-50"
    >
        ⚡ Balance updated
    </div>

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-zinc-800 dark:text-white">User Management</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                {{ $this->users->total() }} users
                @if($this->currentSeason)
                    · active season: <span class="text-amber-400 font-medium">{{ $this->currentSeason->name }}</span>
                @else
                    · <span class="text-zinc-500 italic">no active Forecast season</span>
                @endif
            </p>
        </div>

        {{-- Search --}}
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Search name or email…"
            icon="magnifying-glass"
            class="w-64"
        />
    </div>

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">User</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Role</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Joined</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Comments</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Reactions</th>
                    {{-- Forecast summary columns (only shown when season active) --}}
                    @if($this->currentSeason)
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Faction</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Energy</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Bets</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Net</th>
                    @endif
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($this->users as $user)
                    @php
                        // Load wallet summary for this user (single query per row via eager subquery)
                        $wallet = $this->currentSeason
                            ? $user->forecastWallets()
                                ->where('season_id', $this->currentSeason->id)
                                ->withCount([
                                    'predictions',
                                    'predictions as won_count'  => fn($q) => $q->where('result', 'won'),
                                    'predictions as lost_count' => fn($q) => $q->where('result', 'lost'),
                                    'predictions as pending_count' => fn($q) => $q->where('result', 'pending'),
                                ])
                                ->first()
                            : null;

                        $currencyInfo = $wallet
                            ? (\App\Models\ForecastWallet::CURRENCIES[$wallet->currency] ?? null)
                            : null;

                        $isExpanded = $this->expandedUserId === $user->id;
                    @endphp

                    {{-- ── Main row ──────────────────────────────────────────── --}}
                    <tr
                        class="transition-colors {{ $isExpanded ? 'bg-amber-500/5 dark:bg-amber-500/5' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/40' }}"
                    >
                        {{-- Name + email --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar
                                    size="sm"
                                    :src="$user->profilePhotoUrl()"
                                    :name="$user->name"
                                    color="auto"
                                    :color:seed="$user->id"
                                />
                                <div>
                                    <p class="font-semibold text-zinc-800 dark:text-white">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="ml-1 text-xs text-zinc-400">(you)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Role select --}}
                        <td class="px-4 py-3">
                            @if($user->id === auth()->id())
                                <flux:badge color="red" size="sm">{{ $user->role }}</flux:badge>
                            @else
                                <select
                                    wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                    class="text-xs rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                >
                                    <option value="user"         {{ $user->role === 'user'          ? 'selected' : '' }}>User</option>
                                    <option value="mod"          {{ $user->role === 'mod'           ? 'selected' : '' }}>Moderator</option>
                                    <option value="admin"        {{ $user->role === 'admin'         ? 'selected' : '' }}>Admin</option>
                                    <option value="game_manager" {{ $user->role === 'game_manager'  ? 'selected' : '' }}>Game Manager</option>
                                </select>
                            @endif
                        </td>

                        {{-- Joined --}}
                        <td class="px-4 py-3 text-xs text-zinc-400">
                            {{ $user->created_at->format('Y-m-d') }}
                        </td>

                        {{-- Comments --}}
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-mono text-zinc-400">{{ $user->comments_count }}</span>
                        </td>

                        {{-- Reactions --}}
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-mono text-zinc-400">{{ $user->reactions_count }}</span>
                        </td>

                        @if($this->currentSeason)
                            {{-- Faction (currency icon + label) --}}
                            <td class="px-4 py-3 text-center">
                                @if($wallet && $currencyInfo)
                                    <span title="{{ $currencyInfo['label'] }}" class="text-lg cursor-default">
                                        {{ $currencyInfo['icon'] }}
                                    </span>
                                @else
                                    <span class="text-zinc-600 text-xs italic">—</span>
                                @endif
                            </td>

                            {{-- Balance (energy) --}}
                            <td class="px-4 py-3 text-center">
                                @if($wallet)
                                    <span class="text-sm font-mono font-semibold
                                        {{ $wallet->balance >= 50 ? 'text-emerald-400' : ($wallet->balance >= 20 ? 'text-amber-400' : 'text-red-400') }}">
                                        {{ number_format($wallet->balance, 0) }}
                                    </span>
                                    @if($wallet->resets_count > 0)
                                        <span class="text-[10px] text-zinc-600 ml-1" title="Reset {{ $wallet->resets_count }}×">
                                            ↺{{ $wallet->resets_count }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-zinc-600 text-xs italic">no wallet</span>
                                @endif
                            </td>

                            {{-- Total bets / accuracy --}}
                            <td class="px-4 py-3 text-center">
                                @if($wallet && $wallet->predictions_count > 0)
                                    <p class="text-xs font-mono text-zinc-300">
                                        {{ $wallet->predictions_count }}
                                        <span class="text-zinc-600">total</span>
                                    </p>
                                    @php
                                        $settled = $wallet->won_count + $wallet->lost_count;
                                        $acc = $settled > 0
                                            ? round($wallet->won_count / $settled * 100)
                                            : null;
                                    @endphp
                                    @if($acc !== null)
                                        <p class="text-[10px] text-zinc-500">
                                            {{ $wallet->won_count }}/{{ $settled }} · {{ $acc }}%
                                        </p>
                                    @endif
                                @else
                                    <span class="text-zinc-600 text-xs italic">—</span>
                                @endif
                            </td>

                            {{-- Net profit --}}
                            <td class="px-4 py-3 text-right">
                                @if($wallet && ($wallet->won_count + $wallet->lost_count) > 0)
                                    @php
                                        // Calculate profit inline — avoids N+1 via separate query per wallet
                                        $profit = $wallet->profit();
                                    @endphp
                                    <span class="text-sm font-mono font-bold
                                        {{ $profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $profit >= 0 ? '+' : '' }}{{ number_format($profit, 0) }}
                                    </span>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif
                            </td>
                        @endif

                        {{-- Expand toggle --}}
                        <td class="px-4 py-3 text-right">
                            @if($this->currentSeason)
                                <button
                                    wire:click="toggleExpand({{ $user->id }})"
                                    class="text-xs text-zinc-500 hover:text-zinc-200 transition-colors flex items-center gap-1 ml-auto"
                                    title="{{ $isExpanded ? 'Collapse' : 'Expand forecast detail' }}"
                                >
                                    <svg class="w-4 h-4 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>

                    {{-- ── Expanded forecast detail panel ────────────────────── --}}
                    @if($isExpanded && $this->currentSeason)
                        <tr class="bg-zinc-900/60 dark:bg-zinc-900/80">
                            <td colspan="{{ $this->currentSeason ? 10 : 7 }}" class="px-6 py-5">

                                @if(! $this->expandedWallet)
                                    {{-- User has no wallet in this season --}}
                                    <div class="flex items-center gap-3 text-zinc-500 text-sm">
                                        <span class="text-xl">🌑</span>
                                        <span>
                                            <strong class="text-zinc-300">{{ $user->name }}</strong>
                                            has not joined Koprulu Forecast this season — no wallet created yet.
                                        </span>
                                    </div>

                                @else
                                    @php $w = $this->expandedWallet; @endphp

                                    {{-- ── Stat cards row ──────────────────────────── --}}
                                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-5">
                                        
                                    {{-- Country flag --}}
                                    @if($user->country_code)
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Flag</p>
                                            <img
                                                src="{{ asset('images/country_flags/' . strtolower($user->country_code) . '.svg') }}"
                                                alt="{{ $user->country_code }}"
                                                class="w-8 h-5 rounded-sm mb-1"
                                            >
                                            <p class="text-[10px] text-zinc-500">{{ $user->country_code }}</p>
                                        </div>
                                    @endif

                                    {{-- City --}}
                                    @if($user->city)
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">City</p>
                                            <p class="text-xs font-semibold text-zinc-200 mt-1">{{ $user->city }}</p>
                                        </div>
                                    @endif

                                        {{-- Faction / Perk --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Faction / Perk</p>
                                            @php $ci = \App\Models\ForecastWallet::CURRENCIES[$w->currency] ?? null; @endphp
                                            @if($ci)
                                                <p class="text-lg leading-none mb-1">{{ $ci['icon'] }}</p>
                                                <p class="text-xs font-semibold text-zinc-200">{{ $ci['label'] }}</p>
                                                <p class="text-[10px] text-zinc-500 mt-0.5 leading-tight">{{ $ci['bonus'] }}</p>
                                            @else
                                                <p class="text-xs text-zinc-500 italic">Unknown</p>
                                            @endif
                                        </div>

                                        {{-- Current balance — inline editable --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Balance (Energy)</p>

                                            @if($this->editBalanceUserId === $user->id)
                                                {{-- Edit mode --}}
                                                <div class="flex items-center gap-2 mt-1">
                                                    <input
                                                        type="number"
                                                        wire:model="editBalanceValue"
                                                        wire:keydown.enter="updateBalance"
                                                        wire:keydown.escape="cancelEditBalance"
                                                        min="0"
                                                        max="9999"
                                                        step="1"
                                                        class="w-20 rounded bg-zinc-700 border border-amber-500/50 text-white text-sm font-mono px-2 py-1 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                                        autofocus
                                                    >
                                                    <button
                                                        wire:click="updateBalance"
                                                        class="text-xs px-2 py-1 rounded bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 border border-amber-500/30 transition-colors"
                                                    >Save</button>
                                                    <button
                                                        wire:click="cancelEditBalance"
                                                        class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
                                                    >✕</button>
                                                </div>
                                                @if($this->editBalanceError)
                                                    <p class="text-[10px] text-red-400 mt-1">{{ $this->editBalanceError }}</p>
                                                @endif
                                            @else
                                                {{-- Display mode --}}
                                                <div class="flex items-center gap-2">
                                                    <p class="text-xl font-mono font-bold
                                                        {{ $w->balance >= 50 ? 'text-emerald-400' : ($w->balance >= 20 ? 'text-amber-400' : 'text-red-400') }}">
                                                        {{ number_format($w->balance, 0) }}
                                                    </p>
                                                    <button
                                                        wire:click="startEditBalance({{ $user->id }})"
                                                        class="text-zinc-600 hover:text-amber-400 transition-colors"
                                                        title="Edit balance"
                                                    >
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                                <p class="text-[10px] text-zinc-500">started at 50</p>
                                            @endif
                                        </div>

                                        {{-- Total bets --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Total Bets</p>
                                            <p class="text-xl font-mono font-bold text-zinc-200">{{ $w->stats_total }}</p>
                                            <p class="text-[10px] text-zinc-500">{{ $w->stats_pending }} pending</p>
                                        </div>

                                        {{-- Won / Lost --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Won / Lost</p>
                                            <p class="text-base font-mono font-bold">
                                                <span class="text-emerald-400">{{ $w->stats_won }}</span>
                                                <span class="text-zinc-600"> / </span>
                                                <span class="text-red-400">{{ $w->stats_lost }}</span>
                                            </p>
                                            @if($w->stats_accuracy !== null)
                                                <p class="text-[10px] text-zinc-500">{{ $w->stats_accuracy }}% accuracy</p>
                                            @endif
                                        </div>

                                        {{-- Net profit --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Net Profit</p>
                                            <p class="text-xl font-mono font-bold
                                                {{ $w->stats_profit >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                                {{ $w->stats_profit >= 0 ? '+' : '' }}{{ number_format($w->stats_profit, 0) }}
                                            </p>
                                            <p class="text-[10px] text-zinc-500">from settled bets</p>
                                        </div>

                                        {{-- Resets --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-zinc-700/40 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Wallet Resets</p>
                                            <p class="text-xl font-mono font-bold text-zinc-300">{{ $w->resets_count }}</p>
                                            <p class="text-[10px] text-zinc-500">
                                                @if($w->canReset())
                                                    <span class="text-amber-400">can reset now</span>
                                                @else
                                                    not eligible
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Season --}}
                                        <div class="rounded-lg bg-zinc-800/60 border border-amber-500/20 p-3">
                                            <p class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1">Season</p>
                                            <p class="text-xs font-semibold text-amber-400">{{ $this->currentSeason->name }}</p>
                                            <p class="text-[10px] text-zinc-500">
                                                started {{ \Carbon\Carbon::parse($this->currentSeason->starts_at)->format('d M Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- ── Last 10 bets ──────────────────────────── --}}
                                    @if($w->predictions->isNotEmpty())
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-3">
                                            Last {{ $w->predictions->count() }} bets
                                        </p>

                                        <div class="rounded-lg border border-zinc-700/40 overflow-hidden">
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="bg-zinc-800/80 text-zinc-500">
                                                        <th class="text-left px-3 py-2 font-medium">Match</th>
                                                        <th class="text-left px-3 py-2 font-medium">Pick</th>
                                                        <th class="text-center px-3 py-2 font-medium">Stake</th>
                                                        <th class="text-center px-3 py-2 font-medium">Odds</th>
                                                        <th class="text-center px-3 py-2 font-medium">Perk</th>
                                                        <th class="text-center px-3 py-2 font-medium">Result</th>
                                                        <th class="text-right px-3 py-2 font-medium">Payout</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-zinc-800">
                                                    @foreach($w->predictions as $pred)
                                                        @php
                                                            $isForeigner = $pred->match?->match_type === 'foreigner';
                                                            $nameA = $isForeigner
                                                                ? ($pred->match?->playerA?->name ?? '?')
                                                                : ($pred->match?->player_a_name ?? '?');
                                                            $nameB = $isForeigner
                                                                ? ($pred->match?->playerB?->name ?? '?')
                                                                : ($pred->match?->player_b_name ?? '?');

                                                            $pickedName = $isForeigner
                                                                ? ($pred->pickedPlayer?->name ?? '?')
                                                                : ($pred->pick_side === 'a' ? $nameA : $nameB);

                                                            $resultStyle = match($pred->result) {
                                                                'won'      => 'text-emerald-400',
                                                                'lost'     => 'text-red-400',
                                                                'refunded' => 'text-zinc-500',
                                                                default    => 'text-amber-400',
                                                            };
                                                            $resultIcon = match($pred->result) {
                                                                'won'      => '✓ Won',
                                                                'lost'     => '✗ Lost',
                                                                'refunded' => '↩ Refund',
                                                                default    => '⏳ Pending',
                                                            };
                                                        @endphp
                                                        <tr class="hover:bg-zinc-800/40 transition-colors">
                                                            {{-- Match --}}
                                                            <td class="px-3 py-2 text-zinc-400">
                                                                <span class="text-zinc-200">{{ $nameA }}</span>
                                                                <span class="text-zinc-600 mx-1">vs</span>
                                                                <span class="text-zinc-200">{{ $nameB }}</span>
                                                                <br>
                                                                <span class="text-zinc-600 text-[10px]">
                                                                    {{ $pred->match?->scheduled_at
                                                                        ? \Carbon\Carbon::parse($pred->match->scheduled_at)->format('d M')
                                                                        : '—' }}
                                                                    · {{ $pred->match?->match_type ?? '' }}
                                                                </span>
                                                            </td>

                                                            {{-- Pick --}}
                                                            <td class="px-3 py-2 font-semibold text-zinc-200">
                                                                {{ $pickedName }}
                                                            </td>

                                                            {{-- Stake --}}
                                                            <td class="px-3 py-2 text-center font-mono text-zinc-300">
                                                                {{ number_format($pred->stake, 0) }}
                                                            </td>

                                                            {{-- Odds --}}
                                                            <td class="px-3 py-2 text-center font-mono text-zinc-400">
                                                                ×{{ number_format($pred->odds_at_time, 2) }}
                                                            </td>

                                                            {{-- Perk bonus --}}
                                                            <td class="px-3 py-2 text-center">
                                                                @if($pred->bonus_multiplier > 1)
                                                                    <span class="text-amber-400 font-mono">×{{ number_format($pred->bonus_multiplier, 2) }}</span>
                                                                @else
                                                                    <span class="text-zinc-700">—</span>
                                                                @endif
                                                            </td>

                                                            {{-- Result badge --}}
                                                            <td class="px-3 py-2 text-center">
                                                                <span class="font-semibold {{ $resultStyle }}">{{ $resultIcon }}</span>
                                                            </td>

                                                            {{-- Payout --}}
                                                            <td class="px-3 py-2 text-right font-mono">
                                                                @if($pred->result === 'won')
                                                                    <span class="text-emerald-400 font-bold">+{{ number_format($pred->actual_payout, 0) }}</span>
                                                                @elseif($pred->result === 'pending')
                                                                    <span class="text-amber-400/60">{{ number_format($pred->potential_payout, 0) }}?</span>
                                                                @else
                                                                    <span class="text-zinc-700">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-xs text-zinc-600 italic mt-2">No bets placed yet this season.</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endif

                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ───────────────────────────────────────────────────── --}}
    <div class="mt-4">
        {{ $this->users->links() }}
    </div>
</div>