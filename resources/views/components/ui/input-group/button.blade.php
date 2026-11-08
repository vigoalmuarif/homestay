@use('App\Support\Ui\ButtonVariants')
@props(['type' => 'button', 'variant' => 'ghost', 'size' => 'xs'])
@php
    $sizes = [
        'xs' => "h-7 gap-1 rounded-md px-2 text-xs [&>svg:not([class*='size-'])]:size-3.5",
        'sm' => "h-8 gap-1.5 rounded-lg px-2.5 text-xs [&>svg:not([class*='size-'])]:size-4",
        'icon-xs' => 'size-7 rounded-md p-0 has-[>svg]:p-0',
        'icon-sm' => 'size-8 rounded-lg p-0 has-[>svg]:p-0',
    ];
    $classes = ButtonVariants::classes($variant, 'default', 'flex items-center gap-2 text-sm shadow-none ' . ($sizes[$size] ?? $sizes['xs']) . ' ' . $attributes->get('class'));
@endphp
<button type="{{ $type }}" data-slot="button" data-size="{{ $size }}" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</button>
