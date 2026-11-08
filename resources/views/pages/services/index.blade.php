@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('services.store') }}',
        formData: {
            id: null,
            name: '',
            category: 'meal',
            sub_category: 'breakfast',
            price: '',
            has_stock: false,
            stock_quantity: 0,
            is_active: true,
            description: ''
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
            this.actionUrl = '{{ route('services.store') }}';
            this.formData = {
                id: null,
                name: '',
                category: 'meal',
                sub_category: 'breakfast',
                price: '',
                has_stock: false,
                stock_quantity: 0,
                is_active: true,
                description: ''
            };
            this.showDialog('serviceDialog');
        },
        openEdit(item) {
            this.isEdit = true;
            this.actionUrl = '/services/' + item.id;
            this.formData = {
                id: item.id,
                name: item.name,
                category: item.category,
                sub_category: item.sub_category || '',
                price: item.price,
                has_stock: Boolean(item.has_stock),
                stock_quantity: item.stock_quantity || 0,
                is_active: Boolean(item.is_active),
                description: item.description || ''
            };
            this.showDialog('serviceDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Katalog Layanan, F&B & Mini-POS" />

        <!-- Header Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola menu makanan, minuman minibar, laundry, dan layanan sewa dengan pelacakan stok otomatis.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('service-inclusions.index') }}" variant="outline">
                    Atur Inklusi Paket Kamar &rarr;
                </x-ui.button>

                <x-ui.dialog id="serviceDialog" x-ref="serviceDialog">
                    <x-ui.button type="button" @click="openCreate()">
                        <x-svg.plus class="size-4" />
                        <span>Tambah Layanan / Menu</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-lg">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                <span x-text="isEdit ? 'Edit Layanan / Menu' : 'Tambah Layanan / Menu Baru'"></span>
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Tentukan nama item, kategori, harga jual, serta status pelacakan stok fisik.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form :action="actionUrl" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                            <x-ui.field>
                                <x-ui.field.label for="service-form-name">Nama Item / Layanan <span class="text-error-500">*</span></x-ui.field.label>
                                <x-ui.input id="service-form-name" type="text" name="name" x-model="formData.name" required placeholder="contoh: Nasi Goreng Spesial / Air Mineral 600ml" />
                            </x-ui.field>

                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.field>
                                    <x-ui.field.label for="service-form-category">Kategori <span class="text-error-500">*</span></x-ui.field.label>
                                    <x-ui.select :default-value="'meal'" x-effect="value = formData.category" @change="formData.category = $event.detail.value">
                                        <input type="hidden" name="category" :value="formData.category" />
                                        <x-ui.select.trigger id="service-form-category">
                                            <x-ui.select.value placeholder="Pilih Kategori" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            <x-ui.select.item value="meal">Makanan (Meal)</x-ui.select.item>
                                            <x-ui.select.item value="beverage">Minuman (Beverage)</x-ui.select.item>
                                            <x-ui.select.item value="amenity">Amenitas</x-ui.select.item>
                                            <x-ui.select.item value="rental">Sewa Kendaraan (Rental)</x-ui.select.item>
                                            <x-ui.select.item value="laundry">Laundry</x-ui.select.item>
                                            <x-ui.select.item value="other">Lainnya</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                </x-ui.field>
                                <x-ui.field>
                                    <x-ui.field.label for="service-form-sub-category">Sub Kategori / Waktu</x-ui.field.label>
                                    <x-ui.input id="service-form-sub-category" type="text" name="sub_category" x-model="formData.sub_category" placeholder="breakfast / minibar" />
                                </x-ui.field>
                            </div>

                            <x-ui.field>
                                <x-ui.field.label for="service-form-price">Harga Jual Satuan (Rp) <span class="text-error-500">*</span></x-ui.field.label>
                                <x-ui.input id="service-form-price" type="number" step="500" name="price" x-model="formData.price" required placeholder="30000" />
                            </x-ui.field>

                            <!-- Lacak Stok Fisik Option -->
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-3.5 dark:border-gray-800 dark:bg-white/5 space-y-3">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="has_stock" value="1" x-model="formData.has_stock"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span>Lacak Inventori / Stok Fisik (Minibar, Rental, Kemasan)</span>
                                </label>

                                <div x-show="formData.has_stock" class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <x-ui.field>
                                        <x-ui.field.label for="service-form-stock-quantity">Jumlah Stok Saat Ini (Unit)</x-ui.field.label>
                                        <x-ui.input id="service-form-stock-quantity" type="number" name="stock_quantity" x-model="formData.stock_quantity" min="0" />
                                    </x-ui.field>
                                    <p class="mt-1 text-[11px] text-gray-400">
                                        Stok otomatis berkurang secara atomik di database setiap ada pesanan Room Order.
                                    </p>
                                </div>
                            </div>

                            <x-ui.field>
                                <x-ui.field.label for="service-form-description">Deskripsi / Keterangan Menu</x-ui.field.label>
                                <x-ui.textarea id="service-form-description" name="description" x-model="formData.description" rows="2" placeholder="Komposisi bahan, porsi, catatan layanan..." />
                            </x-ui.field>

                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    Simpan Item Layanan
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

        <!-- 4 Metric Counters -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:gap-4">
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Katalog</div>
                <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['total'] }} Item</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase">Makanan &amp; Sarapan</div>
                <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['meals'] }} Menu</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Minuman / Minibar</div>
                <div class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['beverages'] }} Item</div>
            </x-ui.card>
            <x-ui.card class="p-4">
                <div class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase">Lacak Stok Fisik</div>
                <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['physical_stock'] }} Item</div>
            </x-ui.card>
        </div>

        <!-- Filter & Search Bar -->
        <x-ui.card class="p-4">
            <form method="GET" action="{{ route('services.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <x-ui.input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama menu, minuman, atau layanan..."
                        class="ps-10" />
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                        <x-svg.search class="size-4" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.select :default-value="$category" @change="$refs.filterServiceCategory.value = $event.detail.value; $refs.filterServiceCategory.form.submit()">
                        <input type="hidden" name="category" x-ref="filterServiceCategory" value="{{ $category }}" />
                        <x-ui.select.trigger id="service-filter-category" class="w-48">
                            <x-ui.select.value placeholder="Semua Kategori" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Kategori</x-ui.select.item>
                            <x-ui.select.item value="meal">Makanan (Meal)</x-ui.select.item>
                            <x-ui.select.item value="beverage">Minuman (Beverage)</x-ui.select.item>
                            <x-ui.select.item value="amenity">Amenitas</x-ui.select.item>
                            <x-ui.select.item value="rental">Sewa Kendaraan (Rental)</x-ui.select.item>
                            <x-ui.select.item value="laundry">Laundry</x-ui.select.item>
                            <x-ui.select.item value="other">Lainnya</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.select :default-value="$stockFilter" @change="$refs.filterServiceStock.value = $event.detail.value; $refs.filterServiceStock.form.submit()">
                        <input type="hidden" name="stock" x-ref="filterServiceStock" value="{{ $stockFilter }}" />
                        <x-ui.select.trigger id="service-filter-stock" class="w-44">
                            <x-ui.select.value placeholder="Semua Tipe Stok" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Tipe Stok</x-ui.select.item>
                            <x-ui.select.item value="has_stock">Lacak Stok Fisik</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.button type="submit" variant="outline">
                        Filter
                    </x-ui.button>

                    @if($search || $category !== 'all' || $stockFilter !== 'all')
                        <x-ui.button as="a" href="{{ route('services.index') }}" variant="ghost">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Services Table -->
        <x-ui.card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs text-gray-500 uppercase dark:border-gray-800 dark:bg-white/5 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3.5 font-medium text-start">Nama Item &amp; Keterangan</th>
                            <th class="px-4 py-3.5 font-medium text-start">Kategori</th>
                            <th class="px-4 py-3.5 font-medium text-start">Harga Jual</th>
                            <th class="px-4 py-3.5 font-medium text-start">Lacak Stok Fisik</th>
                            <th class="px-4 py-3.5 font-medium text-start">Status</th>
                            <th class="px-6 py-3.5 font-medium text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($services as $service)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800 dark:text-white/90">
                                        {{ $service->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                        {{ $service->description ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="light" color="gray" size="sm" class="capitalize">
                                        {{ $service->category }}
                                    </x-ui.badge>
                                    @if ($service->sub_category)
                                        <span class="ms-1 text-xs text-gray-400">
                                            ({{ $service->sub_category }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap font-bold text-gray-800 dark:text-white/90">
                                    Rp {{ number_format($service->price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if ($service->has_stock)
                                        <x-ui.badge variant="light" color="primary" size="sm">
                                            Stok: {{ $service->stock_quantity }} Unit
                                        </x-ui.badge>
                                    @else
                                        <span class="text-xs text-gray-400 italic">
                                            Non-Stok (Dapur/Jasa)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <x-ui.badge variant="light" :color="$service->is_active ? 'success' : 'gray'" size="default">
                                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-6 py-4 text-end whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <x-ui.button variant="ghost" size="icon-sm" type="button" :data-item="json_encode($service)" @click="openEdit(JSON.parse($el.dataset.item))" title="Edit Item">
                                            <x-svg.pencil class="size-4" />
                                        </x-ui.button>

                                        <form method="POST" action="{{ route('services.toggle-active', $service) }}" class="inline">
                                            @csrf
                                            <x-ui.button variant="ghost" size="icon-sm" type="submit" title="{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </x-ui.button>
                                        </form>

                                        <form method="POST" action="{{ route('services.destroy', $service) }}" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus item {{ $service->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button variant="ghost" size="icon-sm" type="submit" class="text-rose-500 hover:text-rose-600" title="Hapus Item">
                                                <x-svg.trash-bin class="size-4" />
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    Belum ada data layanan atau menu dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($services->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $services->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>
@endsection
