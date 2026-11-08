<div class="space-y-4">
    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Social Links</h3>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.field x-bind:data-invalid="Boolean(errorOf('facebook'))">
            <x-ui.field.label class="mb-1.5 block">Facebook</x-ui.field.label>
            <x-ui.input type="url" x-model="form.facebook" placeholder="https://facebook.com/username" x-bind:aria-invalid="errorOf('facebook') ? 'true' : null" @blur="touch('facebook')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('facebook')" style="display: none;" x-text="errorOf('facebook')"></p>
        </x-ui.field>

        <x-ui.field x-bind:data-invalid="Boolean(errorOf('x'))">
            <x-ui.field.label class="mb-1.5 block">X.com</x-ui.field.label>
            <x-ui.input type="url" x-model="form.x" placeholder="https://x.com/username" x-bind:aria-invalid="errorOf('x') ? 'true' : null" @blur="touch('x')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('x')" style="display: none;" x-text="errorOf('x')"></p>
        </x-ui.field>

        <x-ui.field x-bind:data-invalid="Boolean(errorOf('linkedin'))">
            <x-ui.field.label class="mb-1.5 block">Linkedin</x-ui.field.label>
            <x-ui.input type="url" x-model="form.linkedin" placeholder="https://linkedin.com/in/username" x-bind:aria-invalid="errorOf('linkedin') ? 'true' : null" @blur="touch('linkedin')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('linkedin')" style="display: none;" x-text="errorOf('linkedin')"></p>
        </x-ui.field>

        <x-ui.field x-bind:data-invalid="Boolean(errorOf('instagram'))">
            <x-ui.field.label class="mb-1.5 block">Instagram</x-ui.field.label>
            <x-ui.input type="url" x-model="form.instagram" placeholder="https://instagram.com/username" x-bind:aria-invalid="errorOf('instagram') ? 'true' : null" @blur="touch('instagram')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('instagram')" style="display: none;" x-text="errorOf('instagram')"></p>
        </x-ui.field>
    </div>
</div>
