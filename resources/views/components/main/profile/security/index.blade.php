@php
    $data = include resource_path('views/components/main/profile/security/data.php');
@endphp

<x-ui.card
    x-data="{ twoFactorEnabled: {{ $data['defaultSecuritySettings']['twoFactorEnabled'] ? 'true' : 'false' }} }"
    {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-700 dark:bg-white/3']) }}
>
    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">Security</h4>

    <div class="mt-1">
        <x-main.profile.security.security-item
            title="Change Password"
            description="Receive real-time notifications and team alerts."
            has-divider
        >
            <x-slot:action>
                <x-ui.dialog>
                    <x-ui.button type="button" variant="outline" class="gap-2" @click="show()">
                        <x-svg.pencil class="size-5" />
                        <span>Change Password</span>
                    </x-ui.button>

                    <x-main.profile.security.change-password-modal />
                </x-ui.dialog>
            </x-slot:action>
        </x-main.profile.security.security-item>

        <x-main.profile.security.security-item
            title="Two-factor authentication (2FA)"
            description="Keep your account secure by enabling 2FA"
        >
            <x-slot:action>
                <x-ui.switch size="sm" model="twoFactorEnabled" aria-label="Toggle two-factor authentication" />
            </x-slot:action>
        </x-main.profile.security.security-item>
    </div>
</x-ui.card>
