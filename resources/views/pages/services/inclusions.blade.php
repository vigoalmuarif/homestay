@extends('layouts.app')

@section('content')
    <div x-data="{
        formData: {
            room_type_id: '{{ $roomTypes->first()?->id ?? '' }}',
            category: 'meal',
            service_id: '',
            session_time: 'breakfast',
            allowance_limit: 0
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
        openAddModal(roomTypeId = null) {
            if (roomTypeId) {
                this.formData.room_type_id = roomTypeId;
            }
            this.formData.category = 'meal';
            this.formData.service_id = '';
            this.formData.session_time = 'breakfast';
            this.formData.allowance_limit = 0;
            
            this.showDialog('inclusionDialog');
            this.$nextTick(() => {
                setTimeout(() => {
                    if (window.initInclusionSelect2) {
                        window.initInclusionSelect2(this.formData.category);
                    }
                }, 100);
            });
        },
        changeCategory() {
            if (window.onInclusionCategoryChange) {
                window.onInclusionCategoryChange(this.formData.category);
            }
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Pengaturan Inklusi Paket Tipe Kamar" />

        <!-- Header Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tentukan hak fasilitas gratis (complimentary breakfast, minibar free allowance, dll.) yang otomatis didapat tamu per tipe kamar.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('services.index') }}" variant="outline">
                    Katalog Item F&amp;B
                </x-ui.button>

                <x-ui.dialog id="inclusionDialog" x-ref="inclusionDialog">
                    <x-ui.button type="button" @click="openAddModal()">
                        <x-svg.plus class="size-4" />
                        <span>Tambah Hak Inklusi Paket</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-lg">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                Tambah Hak Inklusi Paket Kamar
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Tentukan tipe kamar, kategori fasilitas, serta batasan complimentary allowance.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form action="{{ route('service-inclusions.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <x-ui.field>
                                <x-ui.field.label for="inclusion-form-room-type-id">Tipe Kamar <span class="text-error-500">*</span></x-ui.field.label>
                                @if ($roomTypes->count() > 10)
                                    <x-ui.combobox x-effect="value = formData.room_type_id" @change="formData.room_type_id = $event.detail.value">
                                        <input type="hidden" name="room_type_id" :value="formData.room_type_id" />
                                        <x-ui.combobox.input id="inclusion-form-room-type-id" placeholder="Pilih Tipe Kamar..." show-trigger show-clear />
                                        <x-ui.combobox.content>
                                            <x-ui.combobox.empty>Tipe kamar tidak ditemukan.</x-ui.combobox.empty>
                                            @foreach ($roomTypes as $rt)
                                                <x-ui.combobox.item value="{{ $rt->id }}">{{ $rt->name }} (Rp {{ number_format($rt->base_price, 0, ',', '.') }})</x-ui.combobox.item>
                                            @endforeach
                                        </x-ui.combobox.content>
                                    </x-ui.combobox>
                                @else
                                    <x-ui.select x-effect="value = formData.room_type_id" @change="formData.room_type_id = $event.detail.value">
                                        <input type="hidden" name="room_type_id" :value="formData.room_type_id" />
                                        <x-ui.select.trigger id="inclusion-form-room-type-id">
                                            <x-ui.select.value placeholder="Pilih Tipe Kamar" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            @foreach ($roomTypes as $rt)
                                                <x-ui.select.item value="{{ $rt->id }}">{{ $rt->name }} (Rp {{ number_format($rt->base_price, 0, ',', '.') }})</x-ui.select.item>
                                            @endforeach
                                        </x-ui.select.content>
                                    </x-ui.select>
                                @endif
                            </x-ui.field>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <x-ui.field>
                                    <x-ui.field.label for="inclusion-form-category">Kategori Layanan <span class="text-error-500">*</span></x-ui.field.label>
                                    <x-ui.select :default-value="'meal'" x-effect="value = formData.category" @change="formData.category = $event.detail.value; changeCategory()">
                                        <input type="hidden" name="category" :value="formData.category" />
                                        <x-ui.select.trigger id="inclusion-form-category">
                                            <x-ui.select.value placeholder="Pilih Kategori" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            <x-ui.select.item value="meal">Makanan (Resto / Dapur)</x-ui.select.item>
                                            <x-ui.select.item value="beverage">Minuman / Minibar</x-ui.select.item>
                                            <x-ui.select.item value="amenity">Amenitas / Ekstra Bed</x-ui.select.item>
                                            <x-ui.select.item value="laundry">Laundry</x-ui.select.item>
                                            <x-ui.select.item value="rental">Sewa Kendaraan/Alat</x-ui.select.item>
                                            <x-ui.select.item value="any">Semua Kategori (Bebas)</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                </x-ui.field>
                                <x-ui.field>
                                    <x-ui.field.label for="inclusion-form-session-time">Sesi Berlaku</x-ui.field.label>
                                    <x-ui.select :default-value="'breakfast'" x-effect="value = formData.session_time" @change="formData.session_time = $event.detail.value">
                                        <input type="hidden" name="session_time" :value="formData.session_time" />
                                        <x-ui.select.trigger id="inclusion-form-session-time">
                                            <x-ui.select.value placeholder="Pilih Sesi" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            <x-ui.select.item value="breakfast">Sarapan Pagi (Breakfast)</x-ui.select.item>
                                            <x-ui.select.item value="lunch">Makan Siang (Lunch)</x-ui.select.item>
                                            <x-ui.select.item value="dinner">Makan Malam (Dinner)</x-ui.select.item>
                                            <x-ui.select.item value="daily">Harian / Kapan Saja (Daily)</x-ui.select.item>
                                        </x-ui.select.content>
                                    </x-ui.select>
                                </x-ui.field>
                            </div>

                            <x-ui.field>
                                <x-ui.field.label for="select2-service-item">Item Khusus (Pencarian Server-Side)</x-ui.field.label>
                                <div class="mt-1">
                                    <x-common.select2
                                        id="select2-service-item"
                                        name="service_id"
                                        placeholder="-- Berlaku untuk Semua Item pada Kategori di Atas --"
                                        :ajax-url="route('services.search', [], false)"
                                    />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">
                                    Ketik nama item untuk mencari. Kosongkan bila hak berlaku untuk seluruh item dalam kategori terpilih.
                                </p>
                            </x-ui.field>

                            <x-ui.field>
                                <x-ui.field.label for="inclusion-form-allowance-limit">Batas Plafon / Allowance (Rp)</x-ui.field.label>
                                <x-ui.input id="inclusion-form-allowance-limit" type="number" step="1000" min="0" name="allowance_limit" x-model="formData.allowance_limit" placeholder="0" />
                                <p class="mt-1 text-xs text-gray-400">
                                    Ketik <strong class="text-emerald-600">0</strong> untuk 100% gratis penuh (Complimentary tanpa batas plafon).
                                </p>
                            </x-ui.field>

                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    Simpan Inklusi
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

        <!-- Matrix Cards per Room Type -->
        <div class="grid grid-cols-1 gap-6">
            @forelse ($roomTypes as $roomType)
                <x-ui.card class="p-6">
                    <div class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-bold text-gray-800 dark:text-white/90">
                                    {{ $roomType->name }}
                                </h2>
                                <x-ui.badge variant="light" color="primary" size="sm">
                                    {{ $roomType->rooms->count() }} Unit Kamar
                                </x-ui.badge>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Tarif Dasar: <strong class="text-gray-700 dark:text-gray-300">Rp {{ number_format($roomType->base_price, 0, ',', '.') }}</strong> / malam &bull; Kapasitas: {{ $roomType->capacity }} Orang
                            </p>
                        </div>
                        <x-ui.button variant="outline" size="sm" type="button" @click="openAddModal({{ $roomType->id }})">
                            <x-svg.plus class="size-4" />
                            <span>Tambah Inklusi ke Tipe Ini</span>
                        </x-ui.button>
                    </div>

                    <!-- Inclusions List -->
                    <div class="mt-5">
                        @if ($roomType->inclusions->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center dark:border-gray-700">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada fasilitas / inklusi paket terdaftar untuk tipe kamar ini (Room Only).
                                </p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-start text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-xs font-semibold uppercase text-gray-400 dark:border-gray-800">
                                            <th class="py-3 text-start">Layanan / Item Termasuk</th>
                                            <th class="py-3 text-start">Kategori</th>
                                            <th class="py-3 text-start">Sesi Berlaku</th>
                                            <th class="py-3 text-start">Batas Gratis / Plafon</th>
                                            <th class="py-3 text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach ($roomType->inclusions as $inclusion)
                                            <tr>
                                                <td class="py-3 font-medium text-gray-800 dark:text-white/90">
                                                    @if ($inclusion->service)
                                                        <span class="text-brand-600 dark:text-brand-400 font-semibold">{{ $inclusion->service->name }}</span>
                                                        <div class="text-xs text-gray-400">Item Khusus (Harga Normal: Rp {{ number_format($inclusion->service->price, 0, ',', '.') }})</div>
                                                    @else
                                                        <span class="font-semibold text-gray-700 dark:text-gray-300">Semua Item dalam Kategori</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @php
                                                        $catLabels = [
                                                            'meal' => 'Makanan / Resto',
                                                            'beverage' => 'Minuman / Minibar',
                                                            'amenity' => 'Amenitas & Ekstra Bed',
                                                            'rental' => 'Sewa Kendaraan/Alat',
                                                            'laundry' => 'Laundry',
                                                            'any' => 'Semua Kategori (Bebas)',
                                                        ];
                                                    @endphp
                                                    <x-ui.badge variant="light" color="gray" size="sm">
                                                        {{ $catLabels[$inclusion->category] ?? ucfirst($inclusion->category) }}
                                                    </x-ui.badge>
                                                </td>
                                                <td class="py-3 text-gray-600 dark:text-gray-300">
                                                    @if ($inclusion->session_time === 'breakfast')
                                                        <x-ui.badge variant="light" color="warning" size="sm">
                                                            Sarapan Pagi (Breakfast)
                                                        </x-ui.badge>
                                                    @elseif ($inclusion->session_time === 'lunch')
                                                        <x-ui.badge variant="light" color="primary" size="sm">
                                                            Makan Siang (Lunch)
                                                        </x-ui.badge>
                                                    @elseif ($inclusion->session_time === 'dinner')
                                                        <x-ui.badge variant="light" color="primary" size="sm">
                                                            Makan Malam (Dinner)
                                                        </x-ui.badge>
                                                    @elseif ($inclusion->session_time === 'daily')
                                                        <x-ui.badge variant="light" color="success" size="sm">
                                                            Setiap Hari (Daily)
                                                        </x-ui.badge>
                                                    @else
                                                        <span class="text-xs text-gray-500">{{ $inclusion->session_time ?? 'Bebas Kapan Saja' }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    @if ((float)$inclusion->allowance_limit <= 0)
                                                        <x-ui.badge variant="light" color="success" size="sm">
                                                            100% Gratis (Complimentary)
                                                        </x-ui.badge>
                                                    @else
                                                        <x-ui.badge variant="light" color="primary" size="sm">
                                                            Maksimal Rp {{ number_format($inclusion->allowance_limit, 0, ',', '.') }}
                                                        </x-ui.badge>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-end">
                                                    <form action="{{ route('service-inclusions.destroy', $inclusion) }}" method="POST"
                                                        onsubmit="return confirm('Hapus hak inklusi paket ini dari tipe kamar?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-ui.button variant="ghost" size="sm" type="submit" class="text-rose-500 hover:text-rose-600">
                                                            Hapus Hak
                                                        </x-ui.button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card class="p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada tipe kamar yang terdaftar di sistem.</p>
                    <div class="mt-4">
                        <x-ui.button as="a" href="{{ route('room-types.index') }}">
                            Buat Tipe Kamar Terlebih Dahulu &rarr;
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforelse
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        let currentInclusionCategory = 'meal';

        window.initInclusionSelect2 = function(category = 'meal') {
            currentInclusionCategory = category;
            const $ = window.jQuery || window.$;
            if (!$) return;

            const $select = $('#select2-service-item');
            if (!$select.length) return;

            const $dialogContent = $select.closest('[data-slot="dialog-content"]');
            const parentEl = ($dialogContent.length && $dialogContent[0]) ? $dialogContent : $(document.body);

            if (typeof window.initTailAdminSelect2 === 'function') {
                window.initTailAdminSelect2($select, {
                    reinit: true,
                    dropdownParent: parentEl,
                    placeholder: '-- Berlaku untuk Semua Item pada Kategori di Atas --',
                    ajaxUrl: '{{ route("services.search", [], false) }}',
                    ajaxParams: function(params) {
                        const activeCat = $('select[name="category"]').val() || currentInclusionCategory || 'meal';
                        return {
                            category: activeCat
                        };
                    }
                });
            } else if ($.fn.select2) {
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    dropdownParent: parentEl,
                    placeholder: '-- Berlaku untuk Semua Item pada Kategori di Atas --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: '{{ route("services.search", [], false) }}',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            const activeCat = $('select[name="category"]').val() || currentInclusionCategory || 'meal';
                            return {
                                q: params.term || '',
                                category: activeCat,
                                format: 'select2'
                            };
                        },
                        processResults: function (data) {
                            return { results: data.results || [] };
                        },
                        cache: true
                    }
                });
            }
        };

        window.onInclusionCategoryChange = function(category) {
            currentInclusionCategory = category;
            const $ = window.jQuery || window.$;
            if ($) {
                const $select = $('#select2-service-item');
                $select.val(null).trigger('change');
            }
        };
    </script>
@endpush
