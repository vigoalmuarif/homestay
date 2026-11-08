@use('App\Support\TwMerge')
<span {{ $attributes->except('class') }} class="{{ TwMerge::merge("flex items-center gap-2 text-sm font-normal text-gray-500 dark:text-gray-400 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4", $attributes->get('class')) }}">{{ $slot }}</span>
