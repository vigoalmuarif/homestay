@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('room-types.store') }}',
        formData: {
            id: null,
            name: '',
            base_price: '',
            capacity: 2,
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
            this.actionUrl = '{{ route('room-types.store') }}';
            this.formData = {
                id: null,
                name: '',
                base_price: '',
                capacity: 2,
                description: ''
            };
            this.showDialog('roomTypeDialog');
        },
        openEdit(item) {
            this.isEdit = true;
            this.actionUrl = '/room-types/' + item.id;
            this.formData = {
                id: item.id,
                name: item.name,
                base_price: item.base_price,
                capacity: item.capacity,
                description: item.description || ''
            };
            this.showDialog('roomTypeDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Tipe Kamar & Paket" />

        <!-- Header Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola kategori tipe kamar, harga dasar sewa, kapasitas tamu, dan fasilitas kamar homestay.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('rooms.index') }}" variant="outline">
                    Lihat Unit Kamar Fisik &rarr;
                </x-ui.button>

                <x-ui.dialog id="roomTypeDialog" x-ref="roomTypeDialog">
                    <x-ui.button type="button" @click="openCreate()">
                        <x-svg.plus class="size-4" />
                        <span>Tipe Kamar Baru</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-md">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                <span x-text="isEdit ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar Baru'"></span>
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Tentukan parameter tipe kamar dan tarif dasar menginap per malam.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form :action="actionUrl" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                            <x-ui.field>
                                <x-ui.field.label for="room-type-name">Nama Tipe Kamar <span class="text-error-500">*</span></x-ui.field.label>
                                <x-ui.input id="room-type-name" type="text" name="name" x-model="formData.name" required
                                    placeholder="contoh: Deluxe Balcony" />
                            </x-ui.field>

                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.field>
                                    <x-ui.field.label for="room-type-base-price">Harga Dasar / Malam <span class="text-error-500">*</span></x-ui.field.label>
                                    <x-ui.input id="room-type-base-price" type="number" step="1000" name="base_price"
                                        x-model="formData.base_price" required placeholder="500000" />
                                </x-ui.field>
                                <x-ui.field>
                                    <x-ui.field.label for="room-type-capacity">Kapasitas Tamu <span class="text-error-500">*</span></x-ui.field.label>
                                    <x-ui.input id="room-type-capacity" type="number" min="1" max="20" name="capacity"
                                        x-model="formData.capacity" required />
                                </x-ui.field>
                            </div>

                            <x-ui.field>
                                <x-ui.field.label for="room-type-description">Fasilitas / Deskripsi</x-ui.field.label>
                                <x-ui.textarea id="room-type-description" name="description" x-model="formData.description" rows="3"
                                    placeholder="Tipe tempat tidur, fasilitas AC, pemandangan, balkon, dll." />
                            </x-ui.field>

                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    Simpan Tipe Kamar
                                </x-ui.button>
                            </x-ui.dialog.footer>
                        </form>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert
                class="border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                <x-ui.alert.title>Sukses</x-ui.alert.title>
                <x-ui.alert.description>{{ session('success') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Peringatan</x-ui.alert.title>
                <x-ui.alert.description>{{ session('error') }}</x-ui.alert.description>
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

        <!-- Room Types Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($roomTypes as $type)
                <x-ui.card class="justify-between transition hover:border-brand-300 dark:hover:border-brand-800">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">
                                    {{ $type->name }}
                                </h3>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    <x-ui.badge variant="light" color="gray" size="sm">
                                        Kapasitas: {{ $type->capacity }} Tamu
                                    </x-ui.badge>
                                    <x-ui.badge variant="light" color="primary" size="sm">
                                        {{ $type->rooms_count }} Unit
                                    </x-ui.badge>
                                </div>
                            </div>
                            <x-ui.badge variant="light" color="success" size="default">
                                Rp {{ number_format($type->base_price, 0, ',', '.') }}/mlm
                            </x-ui.badge>
                        </div>

                        <p class="mt-4 text-xs text-gray-600 dark:text-gray-300 line-clamp-3">
                            {{ $type->description ?: 'Tidak ada deskripsi fasilitas tambahan.' }}
                        </p>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                        <span class="text-xs text-gray-400">
                            Tipe #{{ $type->id }}
                        </span>
                        <div class="flex items-center gap-2">
                            <x-ui.button variant="outline" size="sm" type="button" :data-item="json_encode($type)" @click="openEdit(JSON.parse($el.dataset.item))">
                                Edit
                            </x-ui.button>
                            <form method="POST" action="{{ route('room-types.destroy', $type) }}" class="inline"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe kamar {{ $type->name }}?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="destructive" size="sm" type="submit">
                                    Hapus
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="p-12 text-center text-gray-500">
                        Belum ada data tipe kamar. Silakan klik tombol <strong>+ Tipe Kamar Baru</strong>.
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        @if ($roomTypes->hasPages())
            <x-ui.card class="px-6 py-4">
                {{ $roomTypes->links() }}
            </x-ui.card>
        @endif

    </div>
@endsection
