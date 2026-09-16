@props(['id', 'title', 'maxWidth' => 'md'])

@php
$widths = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    'full' => 'max-w-full',
];
$maxW = $widths[$maxWidth] ?? $widths['md'];
@endphp

<div x-show="{{ $id }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="{{ $id }}" x-transition.opacity.duration.150ms class="fixed inset-0 bg-ink/40 transition-opacity" @click="{{ $id }} = false"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="{{ $id }}" x-transition.scale.duration.150ms class="inline-block align-bottom bg-paper text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle {{ $maxW }} w-full rounded-2xl border border-rule shadow-pop">
            <div class="px-5 py-3.5 border-b border-rulesoft flex items-center justify-between bg-paper">
                <h3 class="text-[15px] font-semibold text-ink" id="modal-title">{{ $title }}</h3>
                <button @click="{{ $id }} = false" class="text-inkmuted hover:text-ink transition-colors p-1 rounded-md hover:bg-cream2">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-5 py-4 bg-paper">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
