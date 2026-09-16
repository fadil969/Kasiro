@props(['label', 'value', 'color' => 'ink', 'note' => null])

@php
// Statistik minimal: label di atas, angka mono besar, aksen warna bernilai makna.
$colors = [
    'ink'      => 'text-ink',
    'daun'     => 'text-success-600',
    'chili'    => 'text-danger-600',
    'turmeric' => 'text-primary-600',
];
$valueColor = $colors[$color] ?? $colors['ink'];
@endphp

<div class="bg-paper border border-rule rounded-xl shadow-card p-4">
    <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-inkmuted">{{ $label }}</p>
    <p class="font-mono font-bold text-[22px] leading-tight mt-1.5 tnum {{ $valueColor }}">{{ $value }}</p>
    @if($note)
    <p class="text-[11px] text-inkmuted mt-1">{{ $note }}</p>
    @endif
</div>
