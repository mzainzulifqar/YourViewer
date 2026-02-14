@props(['label', 'value', 'delta' => null, 'format' => 'number', 'icon' => 'none', 'tooltip' => '', 'href' => null, 'compareLabel' => 'vs prev period', 'invertDelta' => false])

@php
    $deltaColor = $delta !== null
        ? (($invertDelta ? $delta <= 0 : $delta >= 0) ? 'text-green-600' : 'text-red-500')
        : '';
@endphp

<div class="bg-white rounded-lg border border-gray-200 p-5 cursor-pointer hover:shadow-md hover:border-gray-300 transition-all group relative">
    {{-- Label row --}}
    <div class="flex items-center justify-between mb-1">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <div class="flex items-center gap-1">
            @if($tooltip)
                <span class="group/tip relative cursor-help">
                    <svg class="w-4 h-4 text-gray-300 hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                    <span class="absolute bottom-full right-0 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg w-56 leading-relaxed opacity-0 pointer-events-none group-hover/tip:opacity-100 transition-opacity z-50 shadow-lg">{{ $tooltip }}</span>
                </span>
            @endif
            <svg class="w-4 h-4 text-gray-200 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </div>
    </div>

    {{-- Value --}}
    <p class="text-2xl font-bold text-gray-900 tracking-tight">
        @if($format === 'percent')
            {{ number_format($value * 100, 2) }}%
        @elseif($format === 'duration')
            @php
                $secs = (int) round($value);
                $m = intdiv($secs, 60);
                $s = $secs % 60;
            @endphp
            {{ $m }}m {{ str_pad($s, 2, '0', STR_PAD_LEFT) }}s
        @elseif($format === 'decimal')
            {{ number_format($value, 2) }}
        @else
            {{ number_format($value) }}
        @endif
    </p>

    {{-- Delta --}}
    @if($delta !== null)
        <p class="text-xs mt-1.5">
            <span class="inline-flex items-center gap-0.5 font-medium {{ $deltaColor }}">
                @if(($invertDelta ? $delta <= 0 : $delta >= 0))
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd"/></svg>
                @else
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" clip-rule="evenodd"/></svg>
                @endif
                {{ abs($delta) }}%
            </span>
            <span class="text-gray-400 ml-0.5">{{ $compareLabel }}</span>
        </p>
    @endif
</div>
