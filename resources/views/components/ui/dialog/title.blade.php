@use('App\Support\TwMerge')
<h2 data-slot="dialog-title" x-bind:id="titleId" {{ $attributes->except('class') }} class="{{ TwMerge::merge('text-lg font-semibold text-gray-800 dark:text-white/90', $attributes->get('class')) }}">{{ $slot }}</h2>
