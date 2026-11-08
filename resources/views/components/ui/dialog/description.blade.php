@use('App\Support\TwMerge')
<p data-slot="dialog-description" x-bind:id="descriptionId" {{ $attributes->except('class') }} class="{{ TwMerge::merge('text-sm text-gray-500 dark:text-gray-400', $attributes->get('class')) }}">{{ $slot }}</p>
