@props([
    'startsAt',
    'isStream' => false,
])

@php
    $timestamp = $startsAt->timestamp;
    $colorClass = $isStream ? 'text-purple-300' : 'text-amber-300';
    $colorClassMuted = $isStream ? 'text-purple-300/60' : 'text-amber-300/60';
@endphp

<div class="flex flex-col items-end sm:items-start sm:shrink-0">
    {{-- Sformatowana data + nazwa strefy czasowej (CET/CEST) --}}
<p class="uppercase text-xs sm:text-sm font-mono font-bold {{ $colorClass }}">
    <span x-data
          x-text="new Intl.DateTimeFormat(navigator.language, {
              day: 'numeric',
              month: 'long',
              hour: '2-digit',
              minute: '2-digit',
              timeZone: 'Europe/Warsaw'
          }).format(new Date({{ $timestamp }} * 1000))"></span>
    <span class="opacity-50 text-xs"
      x-data
      x-text="(() => {
          const d = new Date({{ $timestamp }} * 1000);
          const utc = new Date(d.toLocaleString('en-US', { timeZone: 'UTC' }));
          const warsaw = new Date(d.toLocaleString('en-US', { timeZone: 'Europe/Warsaw' }));
          const offsetHours = Math.round((warsaw - utc) / 3600000);
          return offsetHours === 2 ? 'CEST' : 'CET';
      })()"></span>
</p>

    {{-- Countdown timer --}}
    <div class="text-xs sm:text-sm font-mono {{ $colorClassMuted }} tabular-nums"
         x-data="{
             target: {{ $timestamp }},
             intervalId: null,
             d: 0, h: 0, m: 0, s: 0,
             init() {
                 this.tick();
                 this.intervalId = setInterval(() => this.tick(), 1000);
             },
             destroy() {
                 if (this.intervalId) clearInterval(this.intervalId);
             },
             tick() {
                 const diff = this.target - Math.floor(Date.now() / 1000);
                 if (diff <= 0) { this.d = this.h = this.m = this.s = 0; return; }
                 this.d = Math.floor(diff / 86400);
                 this.h = Math.floor((diff % 86400) / 3600);
                 this.m = Math.floor((diff % 3600) / 60);
                 this.s = diff % 60;
             }
         }">
        <span x-show="d > 0" x-text="d + 'd '"></span><span x-text="String(h).padStart(2,'0') + 'h ' + String(m).padStart(2,'0') + 'm ' + String(s).padStart(2,'0') + 's'"></span>
    </div>
</div>