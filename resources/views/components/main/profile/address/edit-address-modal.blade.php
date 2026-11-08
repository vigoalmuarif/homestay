<x-ui.dialog.content class="max-w-[calc(100%-2rem)] rounded-3xl p-6 sm:max-w-146 sm:p-10">
    <x-ui.dialog.header class="gap-2 pe-14 text-start">
        <x-ui.dialog.title class="text-2xl font-semibold text-gray-800 dark:text-white/90">
            Edit Address
        </x-ui.dialog.title>
        <x-ui.dialog.description class="text-sm text-gray-500 dark:text-gray-400">
            Update your details to keep your profile up-to-date.
        </x-ui.dialog.description>
    </x-ui.dialog.header>

    <form
        class="space-y-6"
        novalidate
        x-data="{
            form: {},
            errors: {},
            touched: {},
            validate() {
                const errors = {};
                if (!(this.form.country || '').trim()) errors.country = 'Country is required';
                if (!(this.form.cityState || '').trim()) errors.cityState = 'City/State is required';
                if (!(this.form.postalCode || '').trim()) errors.postalCode = 'Postal Code is required';
                if (!(this.form.taxId || '').trim()) errors.taxId = 'TAX ID is required';
                return errors;
            },
            touch(key) {
                this.touched[key] = true;
                this.errors = this.validate();
            },
            errorOf(key) {
                return this.touched[key] ? this.errors[key] : undefined;
            },
            submit() {
                this.errors = this.validate();
                if (Object.keys(this.errors).length) {
                    Object.keys(this.errors).forEach((k) => (this.touched[k] = true));
                    return false;
                }
                return true;
            },
        }"
        x-effect="if (open) { form = { ...address }; errors = {}; touched = {}; }"
        @submit.prevent="if (submit()) { address = { ...form }; close(); }"
    >
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-6">
            <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('country'))">
                <x-ui.field.label>Country</x-ui.field.label>
                <x-ui.input type="text" x-model="form.country" placeholder="Country" x-bind:aria-invalid="errorOf('country') ? 'true' : null" @blur="touch('country')" />
                <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('country')" style="display: none;" x-text="errorOf('country')"></p>
            </x-ui.field>

            <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('cityState'))">
                <x-ui.field.label>City/State</x-ui.field.label>
                <x-ui.input type="text" x-model="form.cityState" placeholder="City/State" x-bind:aria-invalid="errorOf('cityState') ? 'true' : null" @blur="touch('cityState')" />
                <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('cityState')" style="display: none;" x-text="errorOf('cityState')"></p>
            </x-ui.field>

            <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('postalCode'))">
                <x-ui.field.label>Postal Code</x-ui.field.label>
                <x-ui.input type="text" x-model="form.postalCode" placeholder="Postal Code" x-bind:aria-invalid="errorOf('postalCode') ? 'true' : null" @blur="touch('postalCode')" />
                <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('postalCode')" style="display: none;" x-text="errorOf('postalCode')"></p>
            </x-ui.field>

            <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('taxId'))">
                <x-ui.field.label>TAX ID</x-ui.field.label>
                <x-ui.input type="text" x-model="form.taxId" placeholder="TAX ID" x-bind:aria-invalid="errorOf('taxId') ? 'true' : null" @blur="touch('taxId')" />
                <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('taxId')" style="display: none;" x-text="errorOf('taxId')"></p>
            </x-ui.field>
        </div>

        <x-ui.dialog.footer class="mx-0 mb-0 gap-3 p-0 pt-2 sm:justify-end">
            <x-ui.dialog.close as-child>
                <x-ui.button variant="outline" type="button">Close</x-ui.button>
            </x-ui.dialog.close>
            <x-ui.button type="submit">Save Changes</x-ui.button>
        </x-ui.dialog.footer>
    </form>
</x-ui.dialog.content>
