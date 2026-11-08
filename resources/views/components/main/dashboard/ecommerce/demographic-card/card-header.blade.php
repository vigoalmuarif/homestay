@use('App\Support\Ui\ButtonVariants')
<div class="flex justify-between">
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            {{ __('ecommerce.demographic.title') }}
        </h3>
        <p class="mt-1 text-sm font-normal text-gray-500 dark:text-gray-400">
            {{ __('ecommerce.demographic.subtitle') }}
        </p>
    </div>

    <x-ui.dropdown-menu>
        <x-ui.dropdown-menu.trigger :class="ButtonVariants::classes('ghost', 'icon-xs')">
            <x-svg.horizontal-dots />
        </x-ui.dropdown-menu.trigger>
        <x-ui.dropdown-menu.content align="end" class="w-40">
            <x-ui.dropdown-menu.item>{{ __('common.viewMore') }}</x-ui.dropdown-menu.item>
            <x-ui.dropdown-menu.item>{{ __('common.delete') }}</x-ui.dropdown-menu.item>
        </x-ui.dropdown-menu.content>
    </x-ui.dropdown-menu>
</div>
