@props(['variant' => 'neutral', 'label'])

@php
// Badge: kecil, rounded penuh (pill), tanpa border tebal
$variants = [
    'daun'       => 'bg-success-50 text-success-700',
    'chili'      => 'bg-danger-50 text-danger-700',
    'turmeric'   => 'bg-primary-50 text-primary-700',
    'nontunai'   => 'bg-warning-50 text-warning-700',
    'neutral'    => 'bg-cream2 text-inksoft',
    'solid-daun' => 'bg-success-500 text-white',
    // Alias lama tetap bekerja
    'emerald' => 'bg-success-50 text-success-700',
    'blue'    => 'bg-primary-50 text-primary-700',
    'amber'   => 'bg-primary-50 text-primary-700',
    'red'     => 'bg-danger-50 text-danger-700',
    'slate'   => 'bg-cream2 text-inksoft',
    'purple'  => 'bg-warning-50 text-warning-700',
];
@endphp

<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $variants[$variant] ?? $variants['neutral'] }}">
    {{ $label }}
</span>
