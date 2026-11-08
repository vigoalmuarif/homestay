{{--
    Badge.
    Props: variant (light|solid|ghost), color (primary|success|error|warning|info|gray|light|dark), size (sm|default|lg).
--}}
@use('App\Support\Ui\BadgeVariants')
@props([
    'variant' => 'light',
    'color' => 'primary',
    'size' => 'default',
])
@php
    $classes = BadgeVariants::classes($variant, $color, $size, $attributes->get('class'));
@endphp
<span data-slot="badge" data-variant="{{ $variant }}" data-color="{{ $color }}" data-size="{{ $size }}" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</span>
