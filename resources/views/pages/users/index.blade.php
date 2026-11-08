@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('users.store') }}',
        formData: {
            id: null,
            name: '',
            email: '',
            role: 'receptionist',
            password: '',
            password_confirmation: ''
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
            this.actionUrl = '{{ route('users.store') }}';
            this.formData = {
                id: null,
                name: '',
                email: '',
                role: 'receptionist',
                password: '',
                password_confirmation: ''
            };
            this.showDialog('userDialog');
        },
        openEdit(user, userRole) {
            this.isEdit = true;
            this.actionUrl = '/users/' + user.id;
            this.formData = {
                id: user.id,
                name: user.name,
                email: user.email,
                role: userRole || 'receptionist',
                password: '',
                password_confirmation: ''
            };
            this.showDialog('userDialog');
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Kelola Petugas & Hak Akses" />

        <!-- Header Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola akun petugas operasional homestay, penetapan role hak akses (Admin, Resepsionis, Housekeeping), dan keamanan akun.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.dialog id="userDialog" x-ref="userDialog">
                    <x-ui.button type="button" @click="openCreate()">
                        <x-svg.plus class="size-4" />
                        <span>Tambah Petugas</span>
                    </x-ui.button>

                    <x-ui.dialog.content class="sm:max-w-lg">
                        <x-ui.dialog.header class="text-start pe-10">
                            <x-ui.dialog.title>
                                <span x-text="isEdit ? 'Edit Data Petugas' : 'Tambah Petugas Baru'"></span>
                            </x-ui.dialog.title>
                            <x-ui.dialog.description>
                                <span x-text="isEdit ? 'Perbarui informasi profil atau perbarui role hak akses petugas.' : 'Lengkapi informasi akun baru dan tetapkan role hak aksesnya dalam sistem.'"></span>
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form :action="actionUrl" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                            <!-- Nama Petugas -->
                            <x-ui.field>
                                <x-ui.field.label for="user-form-name">
                                    Nama Lengkap Petugas <span class="text-error-500">*</span>
                                </x-ui.field.label>
                                <x-ui.input id="user-form-name" type="text" name="name" x-model="formData.name" required
                                    placeholder="contoh: Siti Aminah" />
                            </x-ui.field>

                            <!-- Email Petugas -->
                            <x-ui.field>
                                <x-ui.field.label for="user-form-email">
                                    Alamat Email Login <span class="text-error-500">*</span>
                                </x-ui.field.label>
                                <x-ui.input id="user-form-email" type="email" name="email" x-model="formData.email" required
                                    placeholder="contoh: siti@homestay.test" />
                            </x-ui.field>

                            <!-- Role Hak Akses -->
                            <x-ui.field>
                                <x-ui.field.label>
                                    Role Hak Akses <span class="text-error-500">*</span>
                                </x-ui.field.label>
                                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition text-start"
                                        :class="formData.role === 'admin' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-500/10 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'">
                                        <input type="radio" name="role" value="admin" x-model="formData.role" class="sr-only" required>
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-bold text-brand-600 dark:text-brand-400 uppercase tracking-wider">Admin</span>
                                            <span class="size-2 rounded-full" :class="formData.role === 'admin' ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-700'"></span>
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Akses penuh ke semua modul, laporan, dan kelola petugas</span>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition text-start"
                                        :class="formData.role === 'receptionist' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-500/10 ring-1 ring-blue-500' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'">
                                        <input type="radio" name="role" value="receptionist" x-model="formData.role" class="sr-only">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Resepsionis</span>
                                            <span class="size-2 rounded-full" :class="formData.role === 'receptionist' ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-700'"></span>
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Front desk, reservasi tamu, check-in/out, kasir billing</span>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition text-start"
                                        :class="formData.role === 'housekeeping' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-500/10 ring-1 ring-amber-500' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'">
                                        <input type="radio" name="role" value="housekeeping" x-model="formData.role" class="sr-only">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Housekeeping</span>
                                            <span class="size-2 rounded-full" :class="formData.role === 'housekeeping' ? 'bg-amber-500' : 'bg-gray-300 dark:bg-gray-700'"></span>
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">Update status kebersihan kamar fisik & tugas pembersihan</span>
                                    </label>
                                </div>
                            </x-ui.field>

                            <!-- Password & Konfirmasi -->
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <x-ui.field>
                                    <x-ui.field.label for="user-form-password">
                                        <span x-text="isEdit ? 'Password Baru (Opsional)' : 'Password'"></span>
                                        <span x-show="!isEdit" class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <x-ui.input id="user-form-password" type="password" name="password" x-model="formData.password"
                                        x-bind:required="!isEdit" placeholder="Minimal 6 karakter" autocomplete="new-password" />
                                    <p x-show="isEdit" class="mt-1 text-[11px] text-gray-400">Kosongkan jika password tidak diganti.</p>
                                </x-ui.field>

                                <x-ui.field>
                                    <x-ui.field.label for="user-form-password-confirmation">
                                        Konfirmasi Password
                                        <span x-show="!isEdit" class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <x-ui.input id="user-form-password-confirmation" type="password" name="password_confirmation"
                                        x-model="formData.password_confirmation"
                                        placeholder="Ulangi password" autocomplete="new-password" />
                                </x-ui.field>
                            </div>

                            <!-- Action Buttons -->
                            <x-ui.dialog.footer class="pt-2">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button type="button" variant="outline" @click="closeDialog('userDialog')">
                                        Batal
                                    </x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">
                                    <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Petugas'"></span>
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

        @if (session('error'))
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Gagal</x-ui.alert.title>
                <x-ui.alert.description>{{ session('error') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="destructive">
                <x-ui.alert.title>Terjadi Kesalahan</x-ui.alert.title>
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
            <a href="{{ route('users.index') }}" class="block">
                <x-ui.card class="p-4 transition hover:border-gray-400 {{ $roleFilter === 'all' ? 'ring-2 ring-brand-500' : '' }}">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Petugas</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $stats['total'] }} Akun</div>
                    <div class="mt-1 text-xs text-gray-400">Semua staf operasional</div>
                </x-ui.card>
            </a>
            <a href="{{ route('users.index', ['role' => 'admin']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-brand-500 {{ $roleFilter === 'admin' ? 'ring-2 ring-brand-500' : '' }}">
                    <div class="text-xs font-medium text-brand-600 dark:text-brand-400 uppercase">Administrator</div>
                    <div class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['admin'] }} Orang</div>
                    <div class="mt-1 text-xs text-gray-400">Akses penuh sistem</div>
                </x-ui.card>
            </a>
            <a href="{{ route('users.index', ['role' => 'receptionist']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-blue-500 {{ $roleFilter === 'receptionist' ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase">Resepsionis</div>
                    <div class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['receptionist'] }} Orang</div>
                    <div class="mt-1 text-xs text-gray-400">Front desk &amp; billing</div>
                </x-ui.card>
            </a>
            <a href="{{ route('users.index', ['role' => 'housekeeping']) }}" class="block">
                <x-ui.card class="p-4 transition hover:border-amber-500 {{ $roleFilter === 'housekeeping' ? 'ring-2 ring-amber-500' : '' }}">
                    <div class="text-xs font-medium text-amber-600 dark:text-amber-400 uppercase">Housekeeping</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['housekeeping'] }} Orang</div>
                    <div class="mt-1 text-xs text-gray-400">Pembersihan kamar</div>
                </x-ui.card>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <x-ui.card class="p-4">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="relative flex-1">
                    <x-ui.input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari nama petugas atau email..."
                        class="ps-10" />
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none text-gray-400">
                        <x-svg.search class="size-4" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-ui.select :default-value="$roleFilter" @change="$refs.filterRoleInput.value = $event.detail.value; $refs.filterRoleInput.form.submit()">
                        <input type="hidden" name="role" x-ref="filterRoleInput" value="{{ $roleFilter }}" />
                        <x-ui.select.trigger id="user-filter-role" class="w-48">
                            <x-ui.select.value placeholder="Semua Role" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="all">Semua Role</x-ui.select.item>
                            <x-ui.select.item value="admin">Administrator</x-ui.select.item>
                            <x-ui.select.item value="receptionist">Resepsionis</x-ui.select.item>
                            <x-ui.select.item value="housekeeping">Housekeeping</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>

                    <x-ui.button type="submit" variant="outline">
                        Filter
                    </x-ui.button>

                    @if ($search || $roleFilter !== 'all')
                        <x-ui.button as="a" href="{{ route('users.index') }}" variant="ghost">
                            Reset
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </x-ui.card>

        <!-- Users Table Card -->
        <x-ui.card class="p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-start text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-xs font-semibold uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400">
                            <th class="px-5 py-3.5 text-start">Petugas / Akun</th>
                            <th class="px-5 py-3.5 text-start">Role Hak Akses</th>
                            <th class="px-5 py-3.5 text-start">Tanggung Jawab Operasional</th>
                            <th class="px-5 py-3.5 text-start">Terdaftar Sejak</th>
                            <th class="px-5 py-3.5 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            @php
                                $userRole = $user->roles->first()?->name ?? 'none';
                                $roleLabel = match($userRole) {
                                    'admin' => 'Administrator',
                                    'receptionist' => 'Resepsionis',
                                    'housekeeping' => 'Housekeeping',
                                    default => ucfirst($userRole)
                                };
                                $roleBadgeColor = match($userRole) {
                                    'admin' => 'primary',
                                    'receptionist' => 'info',
                                    'housekeeping' => 'warning',
                                    default => 'gray'
                                };
                                $roleDesc = match($userRole) {
                                    'admin' => 'Akses Penuh Seluruh Sistem & Kelola Akun',
                                    'receptionist' => 'Front Desk, Tamu, Billing & Mini-POS',
                                    'housekeeping' => 'Kesiapan & Pembersihan Kamar Fisik',
                                    default => 'Akses terbatas'
                                };
                                $isSelf = (auth()->id() === $user->id);
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/10 dark:text-brand-400">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-800 dark:text-white/90">
                                                    {{ $user->name }}
                                                </span>
                                                @if ($isSelf)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                                        Anda
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <x-ui.badge variant="light" :color="$roleBadgeColor" size="default" class="font-semibold uppercase tracking-wider text-xs">
                                        {{ $roleLabel }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $roleDesc }}
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-end whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <x-ui.button variant="ghost" size="icon-sm" type="button"
                                            :data-user="json_encode($user)"
                                            :data-role="$userRole"
                                            @click="openEdit(JSON.parse($el.dataset.user), $el.dataset.role)"
                                            title="Edit Data Petugas">
                                            <x-svg.pencil class="size-4" />
                                        </x-ui.button>

                                        @if (!$isSelf)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button variant="ghost" size="icon-sm" type="submit"
                                                    class="text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                                                    title="Hapus Petugas"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus petugas {{ addslashes($user->name) }}?')">
                                                    <x-svg.trash-bin class="size-4" />
                                                </x-ui.button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="size-12 rounded-full bg-gray-100 flex items-center justify-center dark:bg-gray-800 text-gray-400 mb-3">
                                            <x-svg.users class="size-6" />
                                        </div>
                                        <p class="font-medium text-gray-700 dark:text-gray-300">Belum ada data petugas</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau filter role.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $users->links() }}
                </div>
            @endif
        </x-ui.card>

    </div>
@endsection
