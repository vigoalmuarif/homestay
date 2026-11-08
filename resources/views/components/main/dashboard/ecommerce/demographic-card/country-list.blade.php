@props(['countries'])
{{-- Only display the top 3 countries in the list --}}
<div class="flex flex-col gap-5">
    @foreach (array_slice($countries, 0, 3) as $country)
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative size-8 shrink-0 overflow-hidden rounded-lg">
                    <img width="32" height="32" src="{{ $country['flag'] }}" alt="{{ __('ecommerce.demographic.' . $country['nameKey']) }}" class="size-full object-cover" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90">
                        {{ __('ecommerce.demographic.' . $country['nameKey']) }}
                    </p>
                    <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">
                        {{ \App\Support\I18n::t('ecommerce.demographic.customers', ['count' => $country['customerCount']]) }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-center">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $country['percentage'] }}
                </p>
            </div>
        </div>
    @endforeach
</div>
