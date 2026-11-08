@use('App\Support\TwMerge')
@props(['orientation' => 'vertical'])
@php
    $orientations = [
        'horizontal' => 'flex-row items-center has-[>[data-slot=field-content]]:items-start *:data-[slot=field-label]:flex-auto has-[>[data-slot=field-content]]:[&>[role=checkbox],[role=radio]]:mt-px',
        'responsive' => 'flex-col *:w-full @md/field-group:flex-row @md/field-group:items-center @md/field-group:*:w-auto @md/field-group:has-[>[data-slot=field-content]]:items-start @md/field-group:*:data-[slot=field-label]:flex-auto [&>.sr-only]:w-auto @md/field-group:has-[>[data-slot=field-content]]:[&>[role=checkbox],[role=radio]]:mt-px',
        'vertical' => 'flex-col *:w-full [&>.sr-only]:w-auto',
    ];
    $classes = TwMerge::merge('group/field flex w-full gap-2 data-[invalid=true]:text-red-500 dark:data-[invalid=true]:text-red-400', $orientations[$orientation] ?? $orientations['vertical'], $attributes->get('class'));
@endphp
<div role="group" data-slot="field" data-orientation="{{ $orientation }}" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</div>
