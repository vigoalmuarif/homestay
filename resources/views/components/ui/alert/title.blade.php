@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        '[&_a]:hover:text-foreground text-base font-medium text-gray-800 group-has-[>svg]/alert:col-start-2 dark:text-white/90 [&_a]:underline [&_a]:underline-offset-3',
        $attributes->get('class')
    );
@endphp
<div data-slot="alert-title" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</div>
