<x-ui.field>
    <x-ui.field.label>Message with Actions</x-ui.field.label>
    <x-ui.input-group class="overflow-hidden">
        <x-ui.input-group.textarea placeholder="Write your message or comment..." rows="4" />
        <x-ui.input-group.addon align="block-end" class="flex w-full items-center justify-between border-t border-gray-200 px-3.5 py-2.5 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <button type="button" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" aria-label="Attach file">
                    <x-svg.paper-clip class="size-4" />
                </button>
                <x-ui.input-group.text class="text-xs text-gray-400">Markdown supported</x-ui.input-group.text>
            </div>
            <x-ui.input-group.button size="xs" variant="default">
                <x-svg.send class="size-3.5" />
                <span>Send</span>
            </x-ui.input-group.button>
        </x-ui.input-group.addon>
    </x-ui.input-group>
</x-ui.field>
