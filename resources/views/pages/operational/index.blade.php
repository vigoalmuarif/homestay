@extends('layouts.app')

@section('content')
    <div x-data="{
        isEdit: false,
        actionUrl: '{{ route('operational.transactions.store') }}',
        formData: {
            id: null,
            type: '{{ $activeType }}',
            title: '',
            category: '',
            amount: '',
            transaction_date: '{{ date('Y-m-d') }}',
            payment_method: 'cash',
            notes: ''
        },
        deleteModalOpen: false,
        deleteActionUrl: '',
        deleteTrxNumber: '',
        incomeCategories: @js($incomeCategories),
        expenseCategories: @js($expenseCategories),

        currentCategories() {
            return this.formData.type === 'income' ? this.incomeCategories : this.expenseCategories;
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
        openCreate(type = '{{ $activeType }}') {
            this.isEdit = false;
            this.actionUrl = '{{ route('operational.transactions.store') }}';
            const categories = type === 'income' ? this.incomeCategories : this.expenseCategories;
            this.formData = {
                id: null,
                type: type,
                title: '',
                category: categories.length > 0 ? categories[0] : '',
                amount: '',
                transaction_date: '{{ date('Y-m-d') }}',
                payment_method: 'cash',
                notes: ''
            };
            this.showDialog('trxDialog');
        },
        openEdit(item) {
            this.isEdit = true;
            this.actionUrl = '/operational/transactions/' + item.id;
            this.formData = {
                id: item.id,
                type: item.type,
                title: item.title,
                category: item.category,
                amount: Math.round(item.amount),
                transaction_date: item.transaction_date ? item.transaction_date.substring(0, 10) : '{{ date('Y-m-d') }}',
                payment_method: item.payment_method || 'cash',
                notes: item.notes || ''
            };
            this.showDialog('trxDialog');
        },
        confirmDelete(item) {
            this.deleteActionUrl = '/operational/transactions/' + item.id;
            this.deleteTrxNumber = item.transaction_number;
            this.deleteModalOpen = true;
        }
    }" class="space-y-6">

        <x-common.page-breadcrumb pageTitle="Operasional &bull; {{ $activeType === 'income' ? 'Pemasukan Umum' : 'Pengeluaran Umum' }}" />

        <!-- Notification Alerts -->
        @if (session('success'))
            <x-ui.alert variant="success">
                <span>{{ session('success') }}</span>
            </x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert variant="error">
                <ul class="list-disc ps-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <!-- Header Navigation & Actions -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pencatatan kas operasional harian homestay: penerimaan pemasukan umum non-kamar dan belanja pengeluaran logistik harian.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @can('create operational')
                    <x-ui.dialog id="trxDialog" x-ref="trxDialog">
                        @if ($activeType === 'income')
                            <x-ui.button type="button" @click="openCreate('income')" class="bg-success-600 hover:bg-success-700 text-white shadow-theme-xs">
                                <x-svg.plus class="size-4" />
                                <span>Tambah Pemasukan Umum</span>
                            </x-ui.button>
                        @else
                            <x-ui.button type="button" @click="openCreate('expense')" class="bg-error-600 hover:bg-error-700 text-white shadow-theme-xs">
                                <x-svg.plus class="size-4" />
                                <span>Tambah Pengeluaran Umum</span>
                            </x-ui.button>
                        @endif

                        <x-ui.dialog.content class="sm:max-w-lg max-h-[90vh] flex flex-col p-0 overflow-hidden">
                            <x-ui.dialog.header class="text-start pe-10 px-6 pt-6 pb-3 shrink-0 border-b border-gray-100 dark:border-gray-800">
                                <x-ui.dialog.title>
                                    <span x-text="isEdit ? 'Edit Transaksi Operasional' : (formData.type === 'income' ? 'Tambah Pemasukan Umum' : 'Tambah Pengeluaran Umum')"></span>
                                </x-ui.dialog.title>
                                <x-ui.dialog.description>
                                    <span x-text="isEdit ? 'Perbarui data transaksi kas operasional homestay.' : 'Catat transaksi penerimaan atau pengeluaran operasional baru secara transparan.'"></span>
                                </x-ui.dialog.description>
                            </x-ui.dialog.header>

                            <form :action="actionUrl" method="POST" class="flex flex-col flex-1 min-h-0">
                                @csrf
                                <input type="hidden" name="_method" value="PUT" :disabled="!isEdit">

                                <!-- Scrollable Body Content -->
                                <div class="px-6 py-4 overflow-y-auto custom-scrollbar flex-1 space-y-4 max-h-[60vh] sm:max-h-[66vh]">
                                    <!-- Jenis Transaksi (Hanya saat create) -->
                                    <x-ui.field x-show="!isEdit">
                                    <x-ui.field.label>
                                        Jenis Transaksi <span class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="relative flex items-center justify-between p-3 rounded-xl border cursor-pointer transition text-start"
                                            :class="formData.type === 'income' ? 'border-success-500 bg-success-50/50 dark:bg-success-500/10 ring-1 ring-success-500' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'">
                                            <input type="radio" name="type" value="income" x-model="formData.type" class="sr-only">
                                            <div>
                                                <span class="block text-xs font-bold text-success-600 dark:text-success-400 uppercase tracking-wider">Pemasukan</span>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400">Kas masuk non-kamar</span>
                                            </div>
                                            <span class="size-2 rounded-full" :class="formData.type === 'income' ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-700'"></span>
                                        </label>

                                        <label class="relative flex items-center justify-between p-3 rounded-xl border cursor-pointer transition text-start"
                                            :class="formData.type === 'expense' ? 'border-error-500 bg-error-50/50 dark:bg-error-500/10 ring-1 ring-error-500' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'">
                                            <input type="radio" name="type" value="expense" x-model="formData.type" class="sr-only">
                                            <div>
                                                <span class="block text-xs font-bold text-error-600 dark:text-error-400 uppercase tracking-wider">Pengeluaran</span>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400">Belanja & kebutuhan logistik</span>
                                            </div>
                                            <span class="size-2 rounded-full" :class="formData.type === 'expense' ? 'bg-error-500' : 'bg-gray-300 dark:bg-gray-700'"></span>
                                        </label>
                                    </div>
                                </x-ui.field>

                                <!-- Tanggal & Metode Bayar -->
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <x-ui.field>
                                        <x-ui.field.label for="trx-date">
                                            Tanggal Transaksi <span class="text-error-500">*</span>
                                        </x-ui.field.label>
                                        <x-ui.input id="trx-date" type="date" name="transaction_date" x-model="formData.transaction_date" required />
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.field.label for="trx-payment-method">
                                            Metode Pembayaran <span class="text-error-500">*</span>
                                        </x-ui.field.label>
                                        <input type="hidden" name="payment_method" :value="formData.payment_method">
                                        <x-ui.select defaultValue="cash"
                                            x-effect="if (formData && formData.payment_method) value = formData.payment_method"
                                            @change="formData.payment_method = $event.detail.value">
                                            <x-ui.select.trigger id="trx-payment-method">
                                                <x-ui.select.value placeholder="Pilih Metode Bayar" />
                                            </x-ui.select.trigger>
                                            <x-ui.select.content>
                                                <x-ui.select.item value="cash">Tunai / Kasir (Cash)</x-ui.select.item>
                                                <x-ui.select.item value="transfer">Transfer Bank</x-ui.select.item>
                                                <x-ui.select.item value="qris">QRIS / E-Wallet</x-ui.select.item>
                                                <x-ui.select.item value="other">Lainnya</x-ui.select.item>
                                            </x-ui.select.content>
                                        </x-ui.select>
                                    </x-ui.field>
                                </div>

                                <!-- Judul / Keperluan Transaksi -->
                                <x-ui.field>
                                    <x-ui.field.label for="trx-title">
                                        Judul / Uraian Keperluan <span class="text-error-500">*</span>
                                    </x-ui.field.label>
                                    <x-ui.input id="trx-title" type="text" name="title" x-model="formData.title" required
                                        ::placeholder="formData.type === 'income' ? 'Contoh: Parkir bus rombongan, Titip bagasi tamu' : 'Contoh: Beli deterjen 5kg & pelembut pakaian, Isi ulang galon'" />
                                </x-ui.field>

                                <!-- Kategori & Nominal -->
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <x-ui.field>
                                        <x-ui.field.label for="trx-category">
                                            Kategori <span class="text-error-500">*</span>
                                        </x-ui.field.label>
                                        <input type="hidden" name="category" :value="formData.category">

                                        <div x-show="formData.type === 'income'">
                                            <x-ui.select :defaultValue="$incomeCategories[0] ?? ''"
                                                x-effect="if (formData && formData.type === 'income' && formData.category) value = formData.category"
                                                @change="formData.category = $event.detail.value">
                                                <x-ui.select.trigger id="trx-category-income">
                                                    <x-ui.select.value placeholder="Pilih Kategori Pemasukan" />
                                                </x-ui.select.trigger>
                                                <x-ui.select.content>
                                                    @foreach ($incomeCategories as $cat)
                                                        <x-ui.select.item value="{{ $cat }}">{{ $cat }}</x-ui.select.item>
                                                    @endforeach
                                                </x-ui.select.content>
                                            </x-ui.select>
                                        </div>

                                        <div x-show="formData.type === 'expense'">
                                            <x-ui.select :defaultValue="$expenseCategories[0] ?? ''"
                                                x-effect="if (formData && formData.type === 'expense' && formData.category) value = formData.category"
                                                @change="formData.category = $event.detail.value">
                                                <x-ui.select.trigger id="trx-category-expense">
                                                    <x-ui.select.value placeholder="Pilih Kategori Pengeluaran" />
                                                </x-ui.select.trigger>
                                                <x-ui.select.content>
                                                    @foreach ($expenseCategories as $cat)
                                                        <x-ui.select.item value="{{ $cat }}">{{ $cat }}</x-ui.select.item>
                                                    @endforeach
                                                </x-ui.select.content>
                                            </x-ui.select>
                                        </div>
                                    </x-ui.field>

                                    <x-ui.field>
                                        <x-ui.field.label for="trx-amount">
                                            Nominal (Rp) <span class="text-error-500">*</span>
                                        </x-ui.field.label>
                                        <x-ui.input id="trx-amount" type="number" min="1" step="1" name="amount" x-model="formData.amount" required
                                            placeholder="50000" />
                                    </x-ui.field>
                                </div>

                                <!-- Catatan Tambahan -->
                                <x-ui.field>
                                    <x-ui.field.label for="trx-notes">
                                        Catatan / Keterangan Tambahan
                                    </x-ui.field.label>
                                    <textarea id="trx-notes" name="notes" x-model="formData.notes" rows="2"
                                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3.5 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                        placeholder="Keterangan nota, toko pembelian, nomor kamar tamu, dll."></textarea>
                                </x-ui.field>

                                </div>

                                <!-- Pinned Footer -->
                                <x-ui.dialog.footer class="mx-0 mb-0 px-6 py-4 shrink-0 border-t border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-900/40 rounded-b-2xl flex flex-row items-center justify-end gap-3">
                                    <x-ui.button type="button" variant="outline" @click="closeDialog('trxDialog')">
                                        Batal
                                    </x-ui.button>
                                    <x-ui.button type="submit"
                                        ::class="formData.type === 'income' ? 'bg-success-600 hover:bg-success-700 text-white' : 'bg-error-600 hover:bg-error-700 text-white'">
                                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Transaksi'"></span>
                                    </x-ui.button>
                                </x-ui.dialog.footer>
                            </form>
                        </x-ui.dialog.content>
                    </x-ui.dialog>
                @endcan

                <a href="{{ route('operational.export-csv', array_merge(request()->query(), ['type' => $activeType])) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition shadow-theme-xs">
                    <x-svg.download class="size-4" />
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs: Pemasukan vs Pengeluaran -->
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="flex gap-4">
                <a href="{{ route('operational.incomes') }}"
                    class="flex items-center gap-2 pb-3.5 pt-1 text-sm font-semibold border-b-2 transition {{ $activeType === 'income' ? 'border-success-500 text-success-600 dark:text-success-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <span class="size-2.5 rounded-full {{ $activeType === 'income' ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                    <span>Pemasukan Umum</span>
                    <span class="rounded-full px-2 py-0.5 text-xs {{ $activeType === 'income' ? 'bg-success-50 text-success-700 dark:bg-success-950/40 dark:text-success-300 font-bold' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ number_format($metrics['income_count']) }}
                    </span>
                </a>

                <a href="{{ route('operational.expenses') }}"
                    class="flex items-center gap-2 pb-3.5 pt-1 text-sm font-semibold border-b-2 transition {{ $activeType === 'expense' ? 'border-error-500 text-error-600 dark:text-error-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                    <span class="size-2.5 rounded-full {{ $activeType === 'expense' ? 'bg-error-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                    <span>Pengeluaran Umum</span>
                    <span class="rounded-full px-2 py-0.5 text-xs {{ $activeType === 'expense' ? 'bg-error-50 text-error-700 dark:bg-error-950/40 dark:text-error-300 font-bold' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ number_format($metrics['expense_count']) }}
                    </span>
                </a>
            </nav>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Pemasukan Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pemasukan Umum</span>
                        <h4 class="mt-2 text-xl font-bold text-success-600 dark:text-success-400">
                            Rp {{ number_format($metrics['total_income'], 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                        <x-svg.arrow-down class="size-5 rotate-180" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $metrics['income_count'] }} transaksi penerimaan tercatat
                </p>
            </div>

            <!-- Pengeluaran Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pengeluaran Umum</span>
                        <h4 class="mt-2 text-xl font-bold text-error-600 dark:text-error-400">
                            Rp {{ number_format($metrics['total_expense'], 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                        <x-svg.arrow-up class="size-5" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $metrics['expense_count'] }} transaksi belanja/biaya tercatat
                </p>
            </div>

            <!-- Net Balance Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo Kas Operasional</span>
                        <h4 class="mt-2 text-xl font-bold {{ $metrics['net_balance'] >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-error-600 dark:text-error-400' }}">
                            {{ $metrics['net_balance'] < 0 ? '-' : '' }}Rp {{ number_format(abs($metrics['net_balance']), 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl {{ $metrics['net_balance'] >= 0 ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400' : 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400' }}">
                        <x-svg.pie-chart class="size-5" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $metrics['net_balance'] >= 0 ? 'Surplus kas operasional periode' : 'Defisit kas operasional periode' }}
                </p>
            </div>

            <!-- Focus Stats Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $activeType === 'income' ? 'Rata-rata Pemasukan' : 'Rata-rata Pengeluaran' }}
                        </span>
                        @php
                            $currentCount = $activeType === 'income' ? $metrics['income_count'] : $metrics['expense_count'];
                            $currentTotal = $activeType === 'income' ? $metrics['total_income'] : $metrics['total_expense'];
                            $avg = $currentCount > 0 ? $currentTotal / $currentCount : 0;
                        @endphp
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">
                            Rp {{ number_format($avg, 0, ',', '.') }}
                        </h4>
                    </div>
                    <div class="flex size-11 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <x-svg.bar-chart class="size-5" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Berdasarkan {{ $currentCount }} data pada filter
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <x-common.component-card title="Filter & Pencarian Kas Operasional" desc="Saring berdasarkan rentang tanggal, kategori, atau metode pembayaran">
            <form method="GET" action="{{ route($activeType === 'income' ? 'operational.incomes' : 'operational.expenses') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                    <!-- Preset Rentang Waktu -->
                    <x-ui.field>
                        <x-ui.field.label>Rentang Waktu</x-ui.field.label>
                        <x-ui.select name="preset" defaultValue="{{ $preset }}" @change="$nextTick(() => $el.closest('form').submit())">
                            <x-ui.select.trigger size="sm">
                                <x-ui.select.value placeholder="Pilih Periode" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="today">Hari Ini</x-ui.select.item>
                                <x-ui.select.item value="last_7_days">7 Hari Terakhir</x-ui.select.item>
                                <x-ui.select.item value="this_month">Bulan Ini</x-ui.select.item>
                                <x-ui.select.item value="all">Semua Periode</x-ui.select.item>
                                <x-ui.select.item value="custom">Kustom Tanggal</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.field>

                    <!-- Kategori -->
                    <x-ui.field>
                        <x-ui.field.label>Kategori Transaksi</x-ui.field.label>
                        <x-ui.select name="category" defaultValue="{{ $category ?? 'all' }}" @change="$nextTick(() => $el.closest('form').submit())">
                            <x-ui.select.trigger size="sm">
                                <x-ui.select.value placeholder="Pilih Kategori" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="all">Semua Kategori</x-ui.select.item>
                                @php
                                    $cats = $activeType === 'income' ? $incomeCategories : $expenseCategories;
                                @endphp
                                @foreach ($cats as $c)
                                    <x-ui.select.item value="{{ $c }}">{{ $c }}</x-ui.select.item>
                                @endforeach
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.field>

                    <!-- Metode Pembayaran -->
                    <x-ui.field>
                        <x-ui.field.label>Metode Bayar</x-ui.field.label>
                        <x-ui.select name="payment_method" defaultValue="{{ $paymentMethod ?? 'all' }}" @change="$nextTick(() => $el.closest('form').submit())">
                            <x-ui.select.trigger size="sm">
                                <x-ui.select.value placeholder="Pilih Metode" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="all">Semua Metode</x-ui.select.item>
                                <x-ui.select.item value="cash">Tunai (Cash)</x-ui.select.item>
                                <x-ui.select.item value="transfer">Transfer Bank</x-ui.select.item>
                                <x-ui.select.item value="qris">QRIS</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.field>

                    <!-- Pencarian Kata Kunci -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">Cari Uraian / No. Trx</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci..."
                                class="w-full rounded-lg border border-gray-300 bg-transparent ps-9 pe-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                <x-svg.search class="size-4" />
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-brand-500 hover:bg-brand-600 text-white transition shadow-theme-xs">
                            <x-svg.filter class="size-4" />
                            <span>Terapkan</span>
                        </button>
                        @if ($search || ($category && $category !== 'all') || ($paymentMethod && $paymentMethod !== 'all') || $preset !== 'this_month')
                            <a href="{{ route($activeType === 'income' ? 'operational.incomes' : 'operational.expenses') }}"
                                class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Custom Date Range Picker inputs if custom preset selected -->
                @if ($preset === 'custom')
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Dari Tanggal:</span>
                            <input type="date" name="start_date" value="{{ $startDate }}"
                                class="rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Sampai Tanggal:</span>
                            <input type="date" name="end_date" value="{{ $endDate }}"
                                class="rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <button type="submit" class="px-3 py-1.5 text-xs rounded-lg bg-gray-800 text-white dark:bg-gray-700 hover:bg-gray-900 transition">
                            Terapkan Tanggal
                        </button>
                    </div>
                @endif
            </form>
        </x-common.component-card>

        <!-- Main Transactions Table -->
        <x-common.component-card :title="$activeType === 'income' ? 'Daftar Pemasukan Umum' : 'Daftar Pengeluaran Umum'" :desc="$activeType === 'income' ? 'Daftar transaksi penerimaan kas non-kamar' : 'Daftar transaksi belanja dan biaya operasional'">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-start">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                No. Transaksi & Tanggal
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $activeType === 'income' ? 'Uraian Pemasukan' : 'Keperluan Pengeluaran' }}
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Kategori
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Metode
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nominal (Rp)
                            </th>
                            <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dicatat Oleh
                            </th>
                            <th scope="col" class="px-4 py-3 text-end text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-transparent">
                        @forelse ($transactions as $item)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition">
                                <!-- No. Transaksi & Tanggal -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-mono text-xs font-bold text-gray-900 dark:text-white">
                                            {{ $item->transaction_number }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $item->transaction_date->format('d M Y') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Judul & Catatan -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col max-w-sm">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                            {{ $item->title }}
                                        </span>
                                        @if ($item->notes)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                                {{ $item->notes }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Kategori -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $activeType === 'income' ? 'bg-success-50 text-success-700 dark:bg-success-950/40 dark:text-success-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $item->category }}
                                    </span>
                                </td>

                                <!-- Metode Pembayaran -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-xs uppercase font-medium text-gray-600 dark:text-gray-400">
                                        {{ $item->payment_method }}
                                    </span>
                                </td>

                                <!-- Nominal -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-sm font-bold {{ $item->type === 'income' ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400' }}">
                                        {{ $item->type === 'income' ? '+' : '-' }}Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </span>
                                </td>

                                <!-- Dicatat Oleh (Petugas + Role) -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="size-7 rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($item->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">
                                                {{ $item->user?->name ?? 'Sistem' }}
                                            </span>
                                            @php
                                                $userRole = $item->user?->roles->first()?->name;
                                            @endphp
                                            <span class="text-[10px] text-gray-400 uppercase">
                                                {{ $userRole === 'receptionist' ? 'Frontdesk' : ($userRole === 'housekeeping' ? 'Housekeeping' : ($userRole === 'admin' ? 'Admin' : 'Staff')) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-end text-sm">
                                    @php
                                        $canEditDelete = auth()->user()->hasRole('admin') || auth()->user()->can('manage operational') || auth()->id() === $item->user_id;
                                    @endphp
                                    @if ($canEditDelete)
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="openEdit({{ json_encode($item) }})"
                                                class="p-1.5 text-gray-500 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                                title="Edit Transaksi">
                                                <x-svg.pencil class="size-4" />
                                            </button>

                                            <button type="button" @click="confirmDelete({{ json_encode($item) }})"
                                                class="p-1.5 text-gray-500 hover:text-error-600 dark:text-gray-400 dark:hover:text-error-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                                title="Hapus Transaksi">
                                                <x-svg.trash-bin class="size-4" />
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="size-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 mb-3">
                                            <x-svg.list class="size-6" />
                                        </div>
                                        <h5 class="text-sm font-semibold text-gray-800 dark:text-white">
                                            Belum ada data {{ $activeType === 'income' ? 'pemasukan umum' : 'pengeluaran umum' }}
                                        </h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">
                                            Tidak ditemukan data yang sesuai dengan filter atau belum ada transaksi yang dicatat.
                                        </p>
                                        @can('create operational')
                                            <button type="button" @click="openCreate('{{ $activeType }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium {{ $activeType === 'income' ? 'bg-success-600 text-white hover:bg-success-700' : 'bg-error-600 text-white hover:bg-error-700' }}">
                                                <x-svg.plus class="size-3.5" />
                                                <span>Catat {{ $activeType === 'income' ? 'Pemasukan' : 'Pengeluaran' }} Sekarang</span>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($transactions->hasPages())
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $transactions->links() }}
                </div>
            @endif
        </x-common.component-card>

        <!-- Delete Confirmation Modal -->
        <div x-show="deleteModalOpen" x-cloak
            class="fixed inset-0 z-9999 flex items-center justify-center bg-black/50 p-4 transition"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div @click.away="deleteModalOpen = false"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-start">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400">
                        <x-svg.octagon-alert class="size-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            Hapus Transaksi Operasional?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Konfirmasi penghapusan catatan kas
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                    Apakah Anda yakin ingin menghapus transaksi <strong class="font-mono text-gray-900 dark:text-white" x-text="deleteTrxNumber"></strong>? Data yang telah dihapus tidak dapat dipulihkan.
                </p>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Batal
                    </button>
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium rounded-lg bg-error-600 hover:bg-error-700 text-white transition shadow-theme-xs">
                            Ya, Hapus Transaksi
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
