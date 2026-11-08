@use('Illuminate\Support\Js')
@php
    $data = include resource_path('views/components/main/profile/personal-information/data.php');
@endphp

<x-ui.card
    x-data="{
        profileData: {{ Js::from($data['defaultPersonalInfo']) }},
        get initialLetter() {
            return this.profileData.firstName && this.profileData.firstName.trim() ? this.profileData.firstName.trim()[0].toUpperCase() : 'M';
        },
        get socialList() {
            const s = this.profileData.socialLinks;
            return [
                { name: 'Facebook', href: s.facebook || '#', hasLink: true, icon: 'facebook' },
                { name: 'X', href: s.x || '#', hasLink: true, icon: 'twitter-x' },
                { name: 'LinkedIn', href: s.linkedin || '#', hasLink: true, icon: 'linkedin' },
                { name: 'Instagram', href: s.instagram || '#', hasLink: true, icon: 'instagram' },
            ];
        },
    }"
    {{ $attributes->merge(['class' => 'relative overflow-hidden border border-gray-200 p-5 sm:p-6 lg:p-7 dark:border-gray-700']) }}
>
    <x-ui.dialog>
        <!-- Top Header Row: Title & Edit Button -->
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-800 sm:text-lg dark:text-white/90">
                Personal Information
            </h2>

            <x-ui.button type="button" variant="outline" size="sm" class="h-9 gap-2 px-3.5" @click="show()">
                <x-svg.pencil class="size-4.5" />
                <span>Edit</span>
            </x-ui.button>
        </div>

        <!-- Profile Pic & Basic Meta -->
        <div class="mt-2 flex items-center gap-4 sm:gap-6">
            <x-ui.avatar class="size-16 shrink-0 sm:size-20">
                <x-ui.avatar.image x-bind:src="profileData.avatarUrl || '/assets/images/user/owner.png'" x-bind:alt="profileData.fullName || 'User profile'" />
                <x-ui.avatar.fallback class="bg-brand-400 text-xl font-medium text-white sm:text-2xl dark:bg-brand-400 dark:text-white">
                    <span x-text="initialLetter"></span>
                </x-ui.avatar.fallback>
            </x-ui.avatar>

            <div class="flex flex-col gap-1">
                <h3 class="text-base font-semibold text-gray-800 sm:text-lg dark:text-white/90" x-text="profileData.fullName"></h3>

                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 sm:text-sm dark:text-gray-400">
                    <span x-text="profileData.role"></span>
                    <span class="inline-block h-3.5 w-px bg-gray-300 dark:bg-gray-700"></span>
                    <span x-text="profileData.location"></span>
                </div>
            </div>
        </div>

        <!-- Details & Social Links Grid -->
        <div class="mt-2 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col gap-6">
                <x-main.profile.personal-information.info-field label="First Name" value="profileData.firstName" />
                <x-main.profile.personal-information.info-field label="Email address" value="profileData.email" />
            </div>

            <div class="flex flex-col gap-6">
                <x-main.profile.personal-information.info-field label="Last Name" value="profileData.lastName" />
                <x-main.profile.personal-information.info-field label="Phone" value="profileData.phone" />
            </div>

            <div class="flex flex-col gap-6">
                <x-main.profile.personal-information.info-field label="Bio" value="profileData.bio" />
            </div>

            <div class="flex flex-col gap-6">
                <x-main.profile.personal-information.social-links />
            </div>
        </div>

        <!-- Edit Personal Information Dialog -->
        <x-main.profile.personal-information.edit-dialog />
    </x-ui.dialog>
</x-ui.card>
