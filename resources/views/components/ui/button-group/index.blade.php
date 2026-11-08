@use('App\Support\TwMerge')
@props(['orientation' => 'horizontal'])
@php
    $base = "flex w-fit items-stretch *:focus-visible:relative *:focus-visible:z-10 has-[>[data-slot=button-group]]:gap-2 has-[select[aria-hidden=true]:last-child]:[&>[data-slot=select-trigger]:last-of-type]:rounded-e-lg [&>[data-slot=select-trigger]:not([class*='w-'])]:w-fit [&>input]:flex-1";
    $orientations = [
        'horizontal' => '[&>[data-slot]:has(~[data-slot])]:rounded-e-none [&>[data-slot]~[data-slot]]:rounded-s-none [&>[data-slot]~[data-slot]]:border-s-0',
        'vertical' => 'flex-col [&>[data-slot]:has(~[data-slot])]:rounded-b-none [&>[data-slot]~[data-slot]]:rounded-t-none [&>[data-slot]~[data-slot]]:border-t-0',
    ];
    $classes = TwMerge::merge($base, $orientations[$orientation] ?? $orientations['horizontal'], $attributes->get('class'));
@endphp
<div role="group" data-slot="button-group" data-orientation="{{ $orientation }}" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</div>
