@props(['type' => 'button', 'variant' => 'primary', 'size' => 'md', 'href' => null])

@php
$variants = [
    // CTA utama — indigo solid
    'primary'   => 'bg-primary-600 hover:bg-primary-700 text-white shadow-card focus:ring-primary-500',
    // Sekunder — putih dengan garis
    'secondary' => 'bg-paper hover:bg-cream2 text-ink border border-rule focus:ring-primary-300',
    // Ghost — tanpa kotak, untuk aksi tersier
    'ghost'     => 'bg-transparent text-inksoft hover:bg-cream2 focus:ring-primary-300',
    // Bahaya — merah
    'danger'    => 'bg-danger-600 hover:bg-danger-700 text-white focus:ring-danger-500',
    // Positif — emerald
    'success'   => 'bg-success-600 hover:bg-success-700 text-white focus:ring-success-500',
];
$sizes = [
    'sm' => 'px-2.5 py-1.5 text-xs',
    'md' => 'px-3.5 py-2 text-[13px]',
    'lg' => 'px-5 py-2.5 text-sm',
    'xl' => 'px-6 py-3 text-base',
];
$classes = "inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-canvas disabled:opacity-50 disabled:cursor-not-allowed "
    . ($variants[$variant] ?? $variants['primary']) . " "
    . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
@endif
