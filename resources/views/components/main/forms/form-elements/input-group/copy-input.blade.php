<x-ui.field x-data="{
    value: 'www.tailadmin.com',
    copied: false,
    async handleCopy() {
        try {
            await navigator.clipboard.writeText(this.value);
            this.copied = true;
            setTimeout(() => (this.copied = false), 2000);
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
    },
}">
    <x-ui.field.label>Website</x-ui.field.label>
    <x-ui.input-group class="overflow-hidden">
        <x-ui.input-group.input x-model="value" type="text" placeholder="www.tailadmin.com" />
        <x-ui.input-group.addon align="inline-end" class="self-stretch border-s border-gray-200 p-0 dark:border-gray-800">
            <button type="button" class="flex h-full cursor-pointer items-center gap-1.5 rounded-e-[10px] px-3.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900 focus:outline-hidden dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white" @click="handleCopy()">
                <template x-if="copied">
                    <span class="flex items-center gap-1.5">
                        <x-svg.check class="size-4 text-green-500" />
                        <span class="text-green-500">Copied!</span>
                    </span>
                </template>
                <template x-if="!copied">
                    <span class="flex items-center gap-1.5">
                        <x-svg.copy class="size-4 text-gray-500 dark:text-gray-400" />
                        <span>Copy</span>
                    </span>
                </template>
            </button>
        </x-ui.input-group.addon>
    </x-ui.input-group>
</x-ui.field>
