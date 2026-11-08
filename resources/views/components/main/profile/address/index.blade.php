@use('Illuminate\Support\Js')
@php
    $data = include resource_path('views/components/main/profile/address/data.php');
@endphp

<x-ui.card
    x-data="{ address: {{ Js::from($data['defaultAddressData']) }} }"
    class="rounded-2xl border border-gray-200 p-5 lg:p-6 dark:border-gray-700"
>
    <div class="flex flex-col gap-6 sm:flex-row lg:items-start lg:justify-between">
        <div class="flex-1">
            <h4 class="mb-4 text-lg font-semibold text-gray-800 lg:mb-6 dark:text-white/90">Address</h4>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                <x-main.profile.address.address-item label="Country" value="address.country" />
                <x-main.profile.address.address-item label="City/State" value="address.cityState" />
                <x-main.profile.address.address-item label="Postal Code" value="address.postalCode" />
                <x-main.profile.address.address-item label="TAX ID" value="address.taxId" />
            </div>
        </div>

        <x-ui.dialog>
            <div>
                <x-ui.button variant="outline" class="w-full lg:w-auto" @click="show()">
                    <x-svg.pencil class="size-5" />
                    Edit
                </x-ui.button>
            </div>

            <x-main.profile.address.edit-address-modal />
        </x-ui.dialog>
    </div>
</x-ui.card>
