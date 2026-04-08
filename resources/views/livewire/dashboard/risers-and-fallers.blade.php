@use('Illuminate\Support\Str')
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    {{-- Risers --}}
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 border-l-4 border-l-green-500 p-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-green-500 mb-3">
            📈 Biggest risers
            <span class="text-zinc-500 font-normal normal-case ml-1">last month</span>
        </p>
        <div class="flex flex-col divide-y divide-zinc-700/50">
            @foreach($this->risers as $row)
            <div class="flex items-center gap-2.5 py-2.5">
                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
                   class="hover:underline font-semibold text-sm text-zinc-100 flex-1 truncate">{{ $row->name }}</a>
                <span class="text-green-400 font-mono text-sm font-bold shrink-0">+{{ $row->rating_change }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Fallers --}}
    <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/40 border-l-4 border-l-red-500 p-4">
        <p class="text-xs font-semibold uppercase tracking-widest text-red-500 mb-3">
            📉 Biggest fallers
            <span class="text-zinc-500 font-normal normal-case ml-1">last month</span>
        </p>
        <div class="flex flex-col divide-y divide-zinc-700/50">
            @foreach($this->fallers as $row)
            <div class="flex items-center gap-2.5 py-2.5">
                <img src="{{ asset('images/country_flags/' . strtolower($row->country_code) . '.svg') }}" class="w-5 h-3.5 rounded-sm shrink-0">
                <a href="{{ route('players.show', ['id' => $row->id, 'slug' => Str::slug($row->name)]) }}"
                   class="hover:underline font-semibold text-sm text-zinc-100 flex-1 truncate">{{ $row->name }}</a>
                <span class="text-red-400 font-mono text-sm font-bold shrink-0">{{ $row->rating_change }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>