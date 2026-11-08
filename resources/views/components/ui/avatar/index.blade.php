@use('App\Support\TwMerge')
@props(['size' => 'default'])
@php
    $classes = TwMerge::merge(
        'group/avatar relative flex size-10 shrink-0 rounded-full select-none data-[size=lg]:size-12 data-[size=sm]:size-8 data-[size=xl]:size-14 data-[size=xs]:size-6 dark:after:mix-blend-lighten',
        $attributes->get('class')
    );
@endphp
{{-- The fallback is shown until the image has loaded. --}}
<span data-slot="avatar" data-size="{{ $size }}" x-data="{ loaded: false }" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</span>
