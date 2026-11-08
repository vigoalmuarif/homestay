@props(['title', 'description', 'buttonText', 'icon' => null, 'iconClass' => '', 'variant' => 'outline', 'hasDivider' => false])

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between gap-4 py-4 first:pt-0 sm:flex-row sm:items-center ' . ($hasDivider ? 'border-b border-gray-200 pb-4 dark:border-gray-700' : '')]) }}>
    <div>
        <h4 class="mb-1 text-base font-medium text-gray-800 dark:text-white/90">
            {{ $title }}
        </h4>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>
    <div class="flex shrink-0 items-center">
        <x-ui.button
            type="button"
            variant="outline"
            class="{{ $variant === 'danger-outline' ? 'border-red-300 text-red-500 shadow-theme-xs hover:bg-red-50 hover:text-red-600 dark:border-red-500/40 dark:text-red-400 dark:hover:bg-red-500/10' : '' }}"
        >
            @if ($icon)
                <x-dynamic-component :component="'svg.' . $icon" :class="$iconClass" />
            @endif
            <span>{{ $buttonText }}</span>
        </x-ui.button>
    </div>
</div>
