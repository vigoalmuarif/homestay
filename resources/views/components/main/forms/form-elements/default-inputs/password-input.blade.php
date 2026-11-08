<x-ui.field x-data="{ showPassword: false }">
    <x-ui.field.label>Password Input</x-ui.field.label>
    <x-ui.input-group>
        <x-ui.input-group.input x-bind:type="showPassword ? 'text' : 'password'" type="password" placeholder="••••••••" />
        <x-ui.input-group.addon align="inline-end">
            <x-ui.input-group.button size="icon-xs" variant="ghost" x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'" aria-label="Show password" @click="showPassword = !showPassword">
                <x-svg.eye x-show="showPassword" style="display: none;" class="size-5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" />
                <x-svg.eye-close x-show="!showPassword" class="size-5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" />
            </x-ui.input-group.button>
        </x-ui.input-group.addon>
    </x-ui.input-group>
</x-ui.field>
