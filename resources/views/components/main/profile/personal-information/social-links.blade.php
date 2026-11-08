<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 sm:gap-3']) }}>
    <p class="text-xs font-normal text-gray-500 sm:text-sm dark:text-gray-400">Social links</p>
    <div class="flex flex-wrap items-center gap-2">
        <template x-for="link in socialList" :key="link.name">
            <div>
                <template x-if="link.icon === 'facebook'">
                    <div>
                        <template x-if="link.hasLink">
                            <x-ui.button variant="outline" size="icon" as-child class="rounded-full">
                                <a :href="link.href" target="_blank" rel="noreferrer" :aria-label="link.name">
                                    <x-svg.facebook class="size-5 fill-current" />
                                </a>
                            </x-ui.button>
                        </template>
                        <template x-if="!link.hasLink">
                            <div :aria-label="link.name" class="flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-theme-xs sm:size-11 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-600">
                                <x-svg.facebook class="size-5 fill-current" />
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="link.icon === 'twitter-x'">
                    <div>
                        <template x-if="link.hasLink">
                            <x-ui.button variant="outline" size="icon" as-child class="rounded-full">
                                <a :href="link.href" target="_blank" rel="noreferrer" :aria-label="link.name">
                                    <x-svg.twitter-x class="size-5 fill-current" />
                                </a>
                            </x-ui.button>
                        </template>
                        <template x-if="!link.hasLink">
                            <div :aria-label="link.name" class="flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-theme-xs sm:size-11 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-600">
                                <x-svg.twitter-x class="size-5 fill-current" />
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="link.icon === 'linkedin'">
                    <div>
                        <template x-if="link.hasLink">
                            <x-ui.button variant="outline" size="icon" as-child class="rounded-full">
                                <a :href="link.href" target="_blank" rel="noreferrer" :aria-label="link.name">
                                    <x-svg.linkedin class="size-5 fill-current" />
                                </a>
                            </x-ui.button>
                        </template>
                        <template x-if="!link.hasLink">
                            <div :aria-label="link.name" class="flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-theme-xs sm:size-11 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-600">
                                <x-svg.linkedin class="size-5 fill-current" />
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="link.icon === 'instagram'">
                    <div>
                        <template x-if="link.hasLink">
                            <x-ui.button variant="outline" size="icon" as-child class="rounded-full">
                                <a :href="link.href" target="_blank" rel="noreferrer" :aria-label="link.name">
                                    <x-svg.instagram class="size-5 fill-current" />
                                </a>
                            </x-ui.button>
                        </template>
                        <template x-if="!link.hasLink">
                            <div :aria-label="link.name" class="flex size-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 shadow-theme-xs sm:size-11 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-600">
                                <x-svg.instagram class="size-5 fill-current" />
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
