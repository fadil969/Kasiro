@props(['label', 'name', 'type' => 'text', 'placeholder' => '', 'value' => '', 'required' => false])

<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-[13px] font-medium text-ink">
        {{ $label }}
        @if($required)
        <span class="text-danger-500">*</span>
        @endif
    </label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           value="{{ $value }}" placeholder="{{ $placeholder }}"
           {{ $required ? 'required' : '' }}
           class="w-full px-3 py-2 rounded-lg border border-rule bg-paper text-sm text-ink placeholder-inkmuted/70 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-colors duration-150"
           {{ $attributes }}>
    @error($name)
    <p class="text-xs text-danger-600 mt-1">{{ $message }}</p>
    @enderror
</div>
