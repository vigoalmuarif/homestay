@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('customers.store') }}',
        formData: {
            id: null,
            full_name: '',
            identity_type: 'ktp',
            identity_number: '',
            phone_number: '',
            email: '',
            country: 'Indonesia',
            address: '',
            special_notes: '',
            is_blacklisted: false
        },
        showDialog(id) {
            if (window.showUiDialog) {
                window.showUiDialog(id);
                return;
            }
            const el = document.getElementById(id) || (this.$refs ? this.$refs[id] : null);
            if (el && window.Alpine) {
                Alpine.$data(el).show();
            }
        },
        closeDialog(id) {
            if (window.closeUiDialog) {
                window.closeUiDialog(id);
                return;
            }
            const el = document.getElementById(id) || (this.$refs ? this.$refs[id] : null);
            if (el && window.Alpine) {
                Alpine.$data(el).close();
            }
        },
        openCreate() {
            this.isEdit = false;
            this.actionUrl = '{{ route('customers.store') }}';
            this.formData = {
                id: null,
                full_name: '',
                identity_type: 'ktp',
                identity_number: '',
                phone_number: '',
                email: '',
                country: 'Indonesia',
                address: '',
                special_notes: '',
                is_blacklisted: false
            };
            this.showDialog('customerDialog');
        },
        openEdit(customer) {
            this.isEdit = true;
            this.actionUrl = '/customers/' + customer.id;
            this.formData = {
                id: customer.id,
                full_name: customer.full_name,
                identity_type: customer.identity_type,
                identity_number: customer.identity_number || '',
                phone_number: customer.phone_number,
                email: customer.email || '',
                country: customer.country || 'Indonesia',
                address: customer.address || '',
                special_notes: customer.special_notes || '',
                is_blacklisted: Boolean(customer.is_blacklisted)
            };
            this.showDialog('customerDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Data Tamu / Customer" />

        <!-- Top Header & Action -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola data identitas tamu, kontak WhatsApp, riwayat domisili, preferensi, dan status blacklist tamu.
                </p>
            </div>
            <div>
                <x-ui.dialog id="customerDialog" x-ref="customerDialog">
                    <x-ui.button type="button" @click="openCreate()">
                        <x-svg.plus class="size-4" />
                        <span>Tamu Baru</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-lg">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                <span x-text="isEdit ? 'Edit Data Tamu' : 'Tambah Tamu Baru'"></span>
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Lengkapi identitas tamu untuk mempermudah reservasi kamar dan pelacakan riwayat menginap.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form :action="actionUrl" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                            <!-- Nama Lengkap -->
                            <x-ui.field>
                                <x-ui.field.label for="customer-form-full-name">
                                    Nama Lengkap Tamu <span class="text-error-500">*</span>
                                </x-ui.field.label>
                                <x-ui.input id="customer-form-full-name" type="text" name="full_name" x-model="formData.full_name" required
                                    placeholder="contoh: Budi Setiawan" />
                            </x-ui.field>

                            <!-- Tipe & Nomor Identitas -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <x-ui.field>
                                    <x-ui.field.label for="customer-form-identity-type">
                                        Tipe Identitas <span class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <x-ui.select :default-value="'ktp'" x-effect="value = formData.identity_type" @change="formData.identity_type = $event.detail.value">
                                        <input type="hidden" name="identity_type" :value="formData.identity_type" />
                                        <x-ui.select.trigger id="customer-form-identity-type">
                                            <x-ui.select.value placeholder="Pilih Tipe Identitas" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            <x-ui.select.item value="ktp">KTP</x-ui.select.item>
                                            <x-ui.select.item value="paspor">Paspor</x-ui.select.item>
                                            <x-ui.select.item value="sim">SIM</x-ui.select.item>
                                            <x-ui.select.item value="other">Lainnya</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                </x-ui.field>
                                <div class="sm:col-span-2">
                                    <x-ui.field>
                                        <x-ui.field.label for="customer-form-identity-number">
                                            Nomor Identitas / NIK
                                        </x-ui.field.label>
                                        <x-ui.input id="customer-form-identity-number" type="text" name="identity_number" x-model="formData.identity_number"
                                            placeholder="Nomor KTP/Paspor/SIM" />
                                    </x-ui.field>
                                </div>
                            </div>

                            <!-- WhatsApp & Email -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <x-ui.field>
                                    <x-ui.field.label for="customer-form-phone-number">
                                        No. WhatsApp / Telepon <span class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <x-ui.input id="customer-form-phone-number" type="text" name="phone_number" x-model="formData.phone_number" required
                                        placeholder="08xxxxxxxxxx" />
                                </x-ui.field>
                                <x-ui.field>
                                    <x-ui.field.label for="customer-form-email">
                                        Alamat Email
                                    </x-ui.field.label>
                                    <x-ui.input id="customer-form-email" type="email" name="email" x-model="formData.email" placeholder="tamu@email.com" />
                                </x-ui.field>
                            </div>

                            <!-- Alamat & Negara -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="sm:col-span-2">
                                    <x-ui.field>
                                        <x-ui.field.label for="customer-form-address">
                                            Alamat Domisili
                                        </x-ui.field.label>
                                        <x-ui.input id="customer-form-address" type="text" name="address" x-model="formData.address"
                                            placeholder="Kota / Alamat lengkap" />
                                    </x-ui.field>
                                </div>
                                <x-ui.field>
                                    <x-ui.field.label for="customer-form-country">
                                        Negara
                                    </x-ui.field.label>
                                    <x-ui.input id="customer-form-country" type="text" name="country" x-model="formData.country" />
                                </x-ui.field>
                            </div>

                            <!-- Catatan Khusus -->
                            <x-ui.field>
                                <x-ui.field.label for="customer-form-special-notes">
                                    Catatan Khusus / Preferensi
                                </x-ui.field.label>
                                <x-ui.textarea id="customer-form-special-notes" name="special_notes" x-model="formData.special_notes" rows="2"
                                    placeholder="Alergi makanan, preferensi bantal/lantai kamar, catatan histori..." />
                            </x-ui.field>

                            <!-- Blacklist Toggle -->
                            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-3.5 dark:border-rose-900/40 dark:bg-rose-500/5">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-rose-800 dark:text-rose-400">
                                    <input type="checkbox" name="is_blacklisted" value="1" x-model="formData.is_blacklisted"
                                        class="rounded border-rose-300 text-rose-600 focus:ring-rose-500" />
                                    <span>Tandai sebagai Tamu Bermasalah (Blacklist)</span>
                                </label>
                            </div>

                            <!-- Action Buttons -->
                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    Simpan Data Tamu
                                </x-ui.button>
                            </x-ui.dialog.footer>
                        </form>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Sukses</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Kesalahan Validasi</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <!-- Metric Counter Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-ui.card class="p-5">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Tamu</span>
                <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</h3>
                <p class="mt-1 text-xs text-gray-500">Terdaftar dalam database</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Tamu Aktif</span>
                <h3 class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</h3>
                <p class="mt-1 text-xs text-emerald-600">Dapat melakukan reservasi</p>
            </x-ui.card>
            <x-ui.card class="p-5">
                <span class="text-xs font-medium text-rose-600 dark:text-rose-400 uppercase">Blacklist</span>
                <h3 class="mt-2 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $stats['blacklisted'] }}</h3>
                <p class="mt-1 text-xs text-rose-600">Dilarang membuat booking</p>
            </x-ui.card>
        </div>

        <!-- Filter & Search Bar -->
        <x-ui.card class="p-4">
            <form method="GET" action="{{ route('customers.index') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <x-ui.input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama tamu, WhatsApp, NIK, atau email..."
                        class="ps-10" />
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                        <x-svg.search class="size-4" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.select :default-value="$status" @change="$refs.filterStatusInput.value = $event.detail.value; $refs.filterStatusInput.form.submit()">
                        <input type="hidden" name="status" x-ref="filterStatusInput" value="{{ $status }}" />
                        <x-ui.select.trigger id="customer-filter-status" class="w-44">
                            <x-ui.select.value placeholder="Semua Status" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Status</x-ui.select.item>
                            <x-ui.select.item value="active">Hanya Aktif</x-ui.select.item>
                            <x-ui.select.item value="blacklisted">Hanya Blacklist</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.button type="submit" variant="outline">
                        Filter
                    </x-ui.button>

                    @if ($search || $status !== 'all')
                        <x-ui.button as="a" href="{{ route('customers.index') }}" variant="ghost">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Customer Table -->
        <x-ui.card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase dark:border-gray-800 dark:bg-white/5 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 font-medium text-start">Nama Tamu &amp; Alamat</th>
                            <th class="px-4 py-3.5 font-medium text-start">Identitas</th>
                            <th class="px-4 py-3.5 font-medium text-start">Kontak</th>
                            <th class="px-4 py-3.5 font-medium text-start">Status</th>
                            <th class="px-6 py-3.5 font-medium text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800 dark:text-white/90">
                                        <a href="{{ route('customers.show', $customer) }}"
                                            class="hover:text-brand-500 transition">
                                            {{ $customer->full_name }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                        {{ $customer->address ?: 'Alamat belum dicatat' }} ({{ $customer->country }})
                                    </div>
                                    @if ($customer->special_notes)
                                        <div class="mt-1 text-[11px] text-amber-600 dark:text-amber-400 italic line-clamp-1">
                                            Catatan: {{ $customer->special_notes }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="light" color="gray" size="sm" class="uppercase">
                                        {{ $customer->identity_type }}
                                    </x-ui.badge>
                                    <div class="mt-1 font-mono text-xs text-gray-700 dark:text-gray-300">
                                        {{ $customer->identity_number ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $customer->phone_number }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $customer->email ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="light" :color="$customer->is_blacklisted ? 'error' : 'success'" size="default">
                                        {{ $customer->is_blacklisted ? 'Blacklist' : 'Aktif' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 text-end whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <x-ui.button variant="ghost" size="icon-sm" type="button" :data-item="json_encode($customer)" @click="openEdit(JSON.parse($el.dataset.item))" title="Edit Data Tamu">
                                            <x-svg.pencil class="size-4" />
                                        </x-ui.button>

                                        <form method="POST" action="{{ route('customers.toggle-blacklist', $customer) }}" class="inline">
                                            @csrf
                                            <x-ui.button variant="ghost" size="icon-sm" type="submit"
                                                class="{{ $customer->is_blacklisted ? 'text-emerald-600 hover:text-emerald-700' : 'text-rose-500 hover:text-rose-600' }}"
                                                title="{{ $customer->is_blacklisted ? 'Batalkan Blacklist' : 'Jadikan Blacklist' }}"
                                                onclick="return confirm('Apakah Anda yakin ingin mengubah status blacklist tamu ini?')">
                                                @if ($customer->is_blacklisted)
                                                    <x-svg.check-circle class="size-4" />
                                                @else
                                                    <x-svg.octagon-alert class="size-4" />
                                                @endif
                                            </x-ui.button>
                                        </form>

                                        <x-ui.button as="a" href="{{ route('customers.show', $customer) }}" variant="ghost" size="icon-sm" title="Lihat Profil">
                                            <x-svg.eye class="size-4" />
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    Belum ada data customer yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $customers->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>
@endsection
