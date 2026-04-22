@props(['event'])

@if($event->isLive())
    <span class="shrink-0 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/40 animate-pulse">
        <span class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></span>
        LIVE
    </span>
@endif

@if($event->isStream())
    <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-500/15 text-purple-300 border border-purple-500/25">
        Stream
    </span>
@else
    <span class="shrink-0 px-1.5 py-0.5 rounded text-xs font-medium bg-amber-500/15 text-amber-300 border border-amber-500/25">
        Open
    </span>
@endif