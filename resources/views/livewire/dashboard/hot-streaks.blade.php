@use('Illuminate\Support\Str')
<div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 border-l-4 border-l-orange-500 p-4">
    <p class="text-xs font-semibold uppercase tracking-widest text-orange-500 mb-3">🔥 Hot streaks</p>
    <div class="flex flex-col divide-y divide-zinc-700/50">
        @foreach($this->streaks as $row)
        <div class="flex items-center gap-2.5 py-2.5">
            <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
            <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
               class="hover:underline font-semibold text-sm text-zinc-100 flex-1 truncate">{{ $row->name }}</a>
            <span class="text-orange-400 font-mono text-sm font-bold shrink-0">{{ $row->streak }}W</span>
        </div>
        @endforeach
    </div>
</div>