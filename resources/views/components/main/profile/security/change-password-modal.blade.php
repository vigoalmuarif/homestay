<x-ui.dialog.content class="max-w-[calc(100%-2rem)] rounded-3xl p-6 sm:max-w-146 sm:p-10">
    <x-ui.dialog.header class="gap-2 pe-14 text-start">
        <x-ui.dialog.title class="text-xl font-semibold text-gray-800 sm:text-2xl dark:text-white/90">
            Change Password
        </x-ui.dialog.title>
        <x-ui.dialog.description class="text-sm text-gray-500 dark:text-gray-400">
            Use a strong password to protect your account
        </x-ui.dialog.description>
    </x-ui.dialog.header>

    <form
        class="space-y-5"
        novalidate
        x-data="{
            form: { currentPassword: '', newPassword: '', confirmPassword: '' },
            errors: {},
            touched: {},
            validate() {
                const errors = {};
                const currentPassword = this.form.currentPassword.trim();
                const newPassword = this.form.newPassword.trim();
                const confirmPassword = this.form.confirmPassword.trim();
                if (!currentPassword) errors.currentPassword = 'Current password is required';
                if (newPassword.length < 8) errors.newPassword = 'Password must be at least 8 characters';
                else if (currentPassword && currentPassword === newPassword) errors.newPassword = 'New password must be different from current password';
                if (!confirmPassword) errors.confirmPassword = 'Please confirm your new password';
                else if (newPassword !== confirmPassword) errors.confirmPassword = 'Passwords do not match';
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
        x-effect="if (open) { form = { currentPassword: '', newPassword: '', confirmPassword: '' }; errors = {}; touched = {}; }"
        @submit.prevent="if (submit()) close()"
    >
        <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('currentPassword'))">
            <x-ui.field.label>Current Password</x-ui.field.label>
            <x-ui.input type="password" x-model="form.currentPassword" placeholder="Enter current password" x-bind:aria-invalid="errorOf('currentPassword') ? 'true' : null" @blur="touch('currentPassword')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('currentPassword')" style="display: none;" x-text="errorOf('currentPassword')"></p>
        </x-ui.field>

        <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('newPassword'))">
            <x-ui.field.label>New Password</x-ui.field.label>
            <x-ui.input type="password" x-model="form.newPassword" placeholder="Enter new password" x-bind:aria-invalid="errorOf('newPassword') ? 'true' : null" @blur="touch('newPassword')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('newPassword')" style="display: none;" x-text="errorOf('newPassword')"></p>
        </x-ui.field>

        <x-ui.field class="gap-1.5" x-bind:data-invalid="Boolean(errorOf('confirmPassword'))">
            <x-ui.field.label>Confirm Password</x-ui.field.label>
            <x-ui.input type="password" x-model="form.confirmPassword" placeholder="Confirm new password" x-bind:aria-invalid="errorOf('confirmPassword') ? 'true' : null" @blur="touch('confirmPassword')" />
            <p class="text-sm font-normal text-red-500 dark:text-red-400" x-show="errorOf('confirmPassword')" style="display: none;" x-text="errorOf('confirmPassword')"></p>
        </x-ui.field>

        <x-ui.dialog.footer class="mx-0 mb-0 gap-3 p-0 pt-2 sm:justify-end">
            <x-ui.dialog.close as-child>
                <x-ui.button variant="outline" type="button">Close</x-ui.button>
            </x-ui.dialog.close>
            <x-ui.button type="submit">Save Changes</x-ui.button>
        </x-ui.dialog.footer>
    </form>
</x-ui.dialog.content>
