@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('rooms.store') }}',
        formData: {
            id: null,
            room_type_id: '{{ $roomTypes->first()->id ?? '' }}',
            room_number: '',
            status: 'available',
            floor: 'Lantai 1',
            notes: ''
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
            this.actionUrl = '{{ route('rooms.store') }}';
            this.formData = {
                id: null,
                room_type_id: '{{ $roomTypes->first()->id ?? '' }}',
                room_number: '',
                status: 'available',
                floor: 'Lantai 1',
                notes: ''
            };
            this.showDialog('roomDialog');
        },
        openEdit(room) {
            this.isEdit = true;
            this.actionUrl = '/rooms/' + room.id;
            this.formData = {
                id: room.id,
                room_type_id: room.room_type_id,
                room_number: room.room_number,
                status: room.status,
                floor: room.floor || 'Lantai 1',
                notes: room.notes || ''
            };
            this.showDialog('roomDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Master Unit Kamar Fisik" />

        <!-- Header Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola unit kamar fisik, status kesiapan kamar, dan pergantian status kebersihan housekeeping.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.button as="a" href="{{ route('room-types.index') }}" variant="outline">
                    Kelola Tipe Kamar &rarr;
                </x-ui.button>

                <x-ui.dialog id="roomDialog" x-ref="roomDialog">
                    <x-ui.button type="button" @click="openCreate()">
                        <x-svg.plus class="size-4" />
                        <span>Unit Kamar Baru</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-md">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                <span x-text="isEdit ? 'Edit Unit Kamar' : 'Tambah Unit Kamar Baru'"></span>
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                Tentukan nomor fisik kamar, tipe kamar, lantai penempatan, serta status kesiapannya.
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form :action="actionUrl" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                            <x-ui.field>
                                <x-ui.field.label for="room-form-type-id">Tipe Kamar <span class="text-error-500">*</span></x-ui.field.label>
                                @if ($roomTypes->count() > 10)
                                    <x-ui.combobox x-effect="value = formData.room_type_id" @change="formData.room_type_id = $event.detail.value">
                                        <input type="hidden" name="room_type_id" :value="formData.room_type_id" />
                                        <x-ui.combobox.input id="room-form-type-id" placeholder="Cari tipe kamar..." show-trigger show-clear />
                                        <x-ui.combobox.content>
                                            <x-ui.combobox.empty>Tipe kamar tidak ditemukan.</x-ui.combobox.empty>
                                            @foreach ($roomTypes as $t)
                                                <x-ui.combobox.item value="{{ $t->id }}">{{ $t->name }} (Rp {{ number_format($t->base_price, 0, ',', '.') }})</x-ui.combobox.item>
                                            @endforeach
                                        </x-ui.combobox.content>
                                    </x-ui.combobox>
                                @else
                                    <x-ui.select x-effect="value = formData.room_type_id" @change="formData.room_type_id = $event.detail.value">
                                        <input type="hidden" name="room_type_id" :value="formData.room_type_id" />
                                        <x-ui.select.trigger id="room-form-type-id">
                                            <x-ui.select.value placeholder="Pilih Tipe Kamar" />
                                        </x-ui.select.trigger>
                                        <x-ui.select.content>
                                            @foreach ($roomTypes as $t)
                                                <x-ui.select.item value="{{ $t->id }}">{{ $t->name }} (Rp {{ number_format($t->base_price, 0, ',', '.') }})</x-ui.select.item>
                                            @endforeach
                                        </x-ui.select.content>
                                    </x-ui.select>
                                @endif
                            </x-ui.field>

                            <div class="grid grid-cols-2 gap-3">
                                <x-ui.field>
                                    <x-ui.field.label for="room-form-number">Nomor Kamar <span class="text-error-500">*</span></x-ui.field.label>
                                    <x-ui.input id="room-form-number" type="text" name="room_number" x-model="formData.room_number" required placeholder="contoh: 101" />
                                </x-ui.field>
                                <x-ui.field>
                                    <x-ui.field.label for="room-form-floor">Lantai / Lokasi</x-ui.field.label>
                                    <x-ui.input id="room-form-floor" type="text" name="floor" x-model="formData.floor" placeholder="Lantai 1" />
                                </x-ui.field>
                            </div>

                            <x-ui.field>
                                <x-ui.field.label for="room-form-status">Status Awal <span class="text-error-500">*</span></x-ui.field.label>
                                <x-ui.select :default-value="'available'" x-effect="value = formData.status" @change="formData.status = $event.detail.value">
                                    <input type="hidden" name="status" :value="formData.status" />
                                    <x-ui.select.trigger id="room-form-status">
                                        <x-ui.select.value placeholder="Pilih Status" />
                                    </x-ui.select.trigger>
                                    <x-ui.select.content>
                                        <x-ui.select.item value="available">Available (Siap Huni)</x-ui.select.item>
                                        <x-ui.select.item value="occupied">Occupied (Sedang Terisi)</x-ui.select.item>
                                        <x-ui.select.item value="dirty">Dirty (Perlu Pembersihan)</x-ui.select.item>
                                        <x-ui.select.item value="maintenance">Maintenance (Dalam Perbaikan)</x-ui.select.item>
                                    </x-ui.select.content>
                                </x-ui.select>
                            </x-ui.field>

                            <x-ui.field>
                                <x-ui.field.label for="room-form-notes">Catatan Kamar</x-ui.field.label>
                                <x-ui.textarea id="room-form-notes" name="notes" x-model="formData.notes" rows="2" placeholder="Catatan kondisi fasilitas, letak kunci, dll." />
                            </x-ui.field>

                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button variant="outline" type="button">Batal</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    Simpan Kamar
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

        <!-- 5 Status Counter Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5 md:gap-4">
            <a href="{{ route('rooms.index') }}" class="block">
                <x-ui.card class="p-4 transition hover:border-brand-500 {{ $status === 'all' ? 'ring-2 ring-brand-500' : '' }}">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Kamar</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['total'] }}</div>
                </x-ui.card>
            </a>
            <a href="{{ route('rooms.index', ['status' => 'available']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-emerald-500 {{ $status === 'available' ? 'ring-2 ring-emerald-500' : '' }}">
                    <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Available (Clean)</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['available'] }}</div>
                </x-ui.card>
            </a>
            <a href="{{ route('rooms.index', ['status' => 'occupied']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-blue-500 {{ $status === 'occupied' ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="text-xs font-medium text-blue-600 dark:text-blue-400">Occupied (Terisi)</div>
                    <div class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['occupied'] }}</div>
                </x-ui.card>
            </a>
            <a href="{{ route('rooms.index', ['status' => 'dirty']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-amber-500 {{ $status === 'dirty' ? 'ring-2 ring-amber-500' : '' }}">
                    <div class="text-xs font-medium text-amber-600 dark:text-amber-400">Dirty (Housekeeping)</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['dirty'] }}</div>
                </x-ui.card>
            </a>
            <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-rose-500 {{ $status === 'maintenance' ? 'ring-2 ring-rose-500' : '' }}">
                    <div class="text-xs font-medium text-rose-600 dark:text-rose-400">Maintenance</div>
                    <div class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $stats['maintenance'] }}</div>
                </x-ui.card>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <x-ui.card class="p-4">
            <form method="GET" action="{{ route('rooms.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <x-ui.input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nomor kamar, lantai, atau tipe..."
                        class="ps-10" />
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                        <x-svg.search class="size-4" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.select :default-value="$status" @change="$refs.filterRoomStatus.value = $event.detail.value; $refs.filterRoomStatus.form.submit()">
                        <input type="hidden" name="status" x-ref="filterRoomStatus" value="{{ $status }}" />
                        <x-ui.select.trigger id="room-filter-status" class="w-40">
                            <x-ui.select.value placeholder="Semua Status" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Status</x-ui.select.item>
                            <x-ui.select.item value="available">Available</x-ui.select.item>
                            <x-ui.select.item value="occupied">Occupied</x-ui.select.item>
                            <x-ui.select.item value="dirty">Dirty</x-ui.select.item>
                            <x-ui.select.item value="maintenance">Maintenance</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.select :default-value="$typeId ?: ''" @change="$refs.filterRoomType.value = $event.detail.value; $refs.filterRoomType.form.submit()">
                        <input type="hidden" name="room_type_id" x-ref="filterRoomType" value="{{ $typeId }}" />
                        <x-ui.select.trigger id="room-filter-type" class="w-48">
                            <x-ui.select.value placeholder="Semua Tipe Kamar" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="">Semua Tipe Kamar</x-ui.select.item>
                            @foreach ($roomTypes as $t)
                                <x-ui.select.item value="{{ $t->id }}">{{ $t->name }}</x-ui.select.item>
                            @endforeach
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.button type="submit" variant="outline">
                        Filter
                    </x-ui.button>

                    @if($search || $status !== 'all' || $typeId)
                        <x-ui.button as="a" href="{{ route('rooms.index') }}" variant="ghost">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Rooms Grid Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
            @forelse ($rooms as $room)
                <x-ui.card class="justify-between p-5 transition hover:border-brand-300 dark:hover:border-brand-800">
                    <div>
                        <!-- Header Card: Room Number & Status Badge -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-extrabold text-gray-800 dark:text-white/90">
                                    {{ $room->room_number }}
                                </span>
                                <x-ui.badge variant="light" color="gray" size="sm">
                                    {{ $room->floor ?: 'Lantai 1' }}
                                </x-ui.badge>
                            </div>

                            @php
                                $badgeColor = match($room->status) {
                                    'available' => 'success',
                                    'occupied' => 'primary',
                                    'dirty' => 'warning',
                                    'cleaning' => 'info',
                                    default => 'error'
                                };
                            @endphp
                            <x-ui.badge variant="light" :color="$badgeColor" size="default" class="capitalize">
                                {{ $room->status }}
                            </x-ui.badge>
                        </div>

                        <!-- Room Details -->
                        <div class="mt-3">
                            <div class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                {{ $room->roomType->name }}
                            </div>
                            <div class="text-xs text-brand-600 dark:text-brand-400 font-bold mt-0.5">
                                Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }} / malam
                            </div>
                        </div>

                        @if ($room->notes)
                            <div class="mt-3 rounded-lg bg-gray-50 p-2 text-[11px] text-gray-600 dark:bg-white/5 dark:text-gray-400 line-clamp-2">
                                {{ $room->notes }}
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer: Quick Status Switcher & Actions -->
                    <div class="mt-5 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-2">
                            <!-- Fast Status Switcher Form -->
                            <form method="POST" action="{{ route('rooms.update-status', $room) }}" class="flex-1">
                                @csrf
                                <x-ui.select :default-value="$room->status" size="sm" @change="$el.closest('form').querySelector('input[name=status]').value = $event.detail.value; $el.closest('form').submit()">
                                    <input type="hidden" name="status" value="{{ $room->status }}" />
                                    <x-ui.select.trigger size="sm" class="h-8 py-1 ps-2.5 pe-2 text-xs font-medium">
                                        <x-ui.select.value placeholder="Status" />
                                    </x-ui.select.trigger>
                                    <x-ui.select.content>
                                        <x-ui.select.item value="available">&bull; Available</x-ui.select.item>
                                        <x-ui.select.item value="occupied">&bull; Occupied</x-ui.select.item>
                                        <x-ui.select.item value="dirty">&bull; Dirty</x-ui.select.item>
                                        <x-ui.select.item value="cleaning">&bull; Cleaning</x-ui.select.item>
                                        <x-ui.select.item value="maintenance">&bull; Maintenance</x-ui.select.item>
                                    </x-ui.select.content>
                                </x-ui.select>
                            </form>

                            <div class="flex items-center gap-1">
                                <x-ui.button variant="ghost" size="icon-sm" type="button" :data-item="json_encode($room)" @click="openEdit(JSON.parse($el.dataset.item))" title="Edit Unit Kamar">
                                    <x-svg.pencil class="size-4" />
                                </x-ui.button>
                                <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kamar {{ $room->room_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="icon-sm" type="submit" class="text-rose-500 hover:text-rose-600" title="Hapus Unit Kamar">
                                        <x-svg.trash-bin class="size-4" />
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="col-span-full">
                    <x-ui.card class="p-12 text-center text-gray-500">
                        Belum ada data unit kamar yang sesuai filter.
                    </x-ui.card>
                </div>
            @endforelse
        </div>

        @if ($rooms->hasPages())
            <x-ui.card class="px-6 py-4">
                {{ $rooms->links() }}
            </x-ui.card>
        @endif

    </div>
@endsection
