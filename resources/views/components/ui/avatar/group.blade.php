@use('App\Support\TwMerge')
@php
    $classes = TwMerge::merge(
        'group/avatar-group flex -space-x-2 *:data-[slot=avatar]:ring-2 *:data-[slot=avatar]:ring-white dark:*:data-[slot=avatar]:ring-gray-800 dark:**:data-[slot=avatar-fallback]:bg-[#192130]!',
        $attributes->get('class')
    );
@endphp
<div data-slot="avatar-group" {{ $attributes->except('class') }} class="{{ $classes }}">{{ $slot }}</div>
