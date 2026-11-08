<x-ui.dialog.content class="max-h-[90vh] w-full overflow-hidden p-0 sm:max-w-2xl">
    <x-ui.scroll-area class="max-h-[90vh]" viewport-class="max-h-[90vh]">
        <div class="p-6 sm:p-8">
            <x-ui.dialog.header class="gap-1 sm:gap-2">
                <x-ui.dialog.title class="text-xl font-semibold text-gray-800 sm:text-2xl dark:text-white/90">
                    Edit Personal Information
                </x-ui.dialog.title>
                <x-ui.dialog.description class="text-sm text-gray-500 dark:text-gray-400">
                    Update your details to keep your profile up-to-date.
                </x-ui.dialog.description>
            </x-ui.dialog.header>

            <form
                class="mt-4 space-y-6"
                novalidate
                x-data="{
                    form: {},
                    errors: {},
                    touched: {},
                    avatarFileUrl: null,
                    get previewAvatarUrl() {
                        return this.avatarFileUrl || this.form.avatarUrl || '/assets/images/user/user-01.jpg';
                    },
                    validate() {
                        const errors = {};
                        const f = this.form;
                        if (!(f.firstName || '').trim()) errors.firstName = 'First name is required';
                        if (!(f.lastName || '').trim()) errors.lastName = 'Last name is required';
                        if (!(f.email || '').trim()) errors.email = 'Email is required';
                        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email.trim())) errors.email = 'Please enter a valid email address';
                        if (!(f.phone || '').trim()) errors.phone = 'Phone number is required';
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
                            return null;
                        }
                        const f = this.form;
                        const fullName = `${f.firstName.trim()} ${f.lastName.trim()}`.trim();
                        return {
                            avatarUrl: this.previewAvatarUrl,
                            fullName: fullName || profileData.fullName,
                            role: profileData.role,
                            location: profileData.location,
                            firstName: f.firstName.trim(),
                            lastName: f.lastName.trim(),
                            email: f.email.trim(),
                            phone: f.phone.trim(),
                            bio: (f.bio || '').trim(),
                            socialLinks: {
                                facebook: (f.facebook || '').trim(),
                                x: (f.x || '').trim(),
                                linkedin: (f.linkedin || '').trim(),
                                instagram: (f.instagram || '').trim(),
                            },
                        };
                    },
                }"
                x-effect="if (open) { form = { firstName: profileData.firstName, lastName: profileData.lastName, email: profileData.email, phone: profileData.phone, bio: profileData.bio, facebook: profileData.socialLinks.facebook, x: profileData.socialLinks.x, linkedin: profileData.socialLinks.linkedin, instagram: profileData.socialLinks.instagram, avatarUrl: profileData.avatarUrl }; errors = {}; touched = {}; avatarFileUrl = null; }"
                @submit.prevent="const next = submit(); if (next) { profileData = next; close(); }"
            >
                <x-main.profile.personal-information.edit-dialog.avatar-upload-section />

                <x-main.profile.personal-information.edit-dialog.personal-details-fields />

                <x-main.profile.personal-information.edit-dialog.social-links-fields />

                <!-- Footer Actions -->
                <div class="flex flex-col-reverse justify-end gap-3 pt-3 sm:flex-row">
                    <x-ui.button type="button" variant="outline" @click="close()">
                        Close
                    </x-ui.button>
                    <x-ui.button type="submit">Save Changes</x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.scroll-area>
</x-ui.dialog.content>
