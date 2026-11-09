<?php

namespace App\Http\Controllers;

use App\Models\OperationalTransaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationalTransactionController extends Controller
{
    /**
     * Operational home / redirect to incomes or overview.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('operational.incomes');
    }

    /**
     * Display list of General Incomes (Pemasukan Umum).
     */
    public function incomes(Request $request): View
    {
        return $this->renderView($request, 'income');
    }

    /**
     * Display list of General Expenses (Pengeluaran Umum).
     */
    public function expenses(Request $request): View
    {
        return $this->renderView($request, 'expense');
    }

    /**
     * Shared render logic for operational transaction views.
     */
    protected function renderView(Request $request, string $activeType): View
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $paymentMethod = $request->query('payment_method');
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];
        $preset = $dates['preset'];

        // Base query for current type
        $query = OperationalTransaction::with('user')
            ->where('type', $activeType);

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);
        }

        if (filled($search)) {
            $query->search($search);
        }

        if (filled($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        if (filled($paymentMethod) && $paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        $transactions = $query->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Calculate overarching KPI metrics for period
        $statsQuery = OperationalTransaction::query();
        if ($startDate && $endDate) {
            $statsQuery->whereBetween('transaction_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);
        }

        $allInPeriod = $statsQuery->get();
        $totalPeriodIncome = (float) $allInPeriod->where('type', 'income')->sum('amount');
        $totalPeriodExpense = (float) $allInPeriod->where('type', 'expense')->sum('amount');
        $netBalance = $totalPeriodIncome - $totalPeriodExpense;

        $incomeCount = $allInPeriod->where('type', 'income')->count();
        $expenseCount = $allInPeriod->where('type', 'expense')->count();

        $incomeCategories = OperationalTransaction::defaultIncomeCategories();
        $expenseCategories = OperationalTransaction::defaultExpenseCategories();

        $pageTitle = $activeType === 'income' ? 'List Pemasukan Umum' : 'List Pengeluaran Umum';

        return view('pages.operational.index', [
            'title' => $pageTitle,
            'activeType' => $activeType,
            'transactions' => $transactions,
            'search' => $search,
            'category' => $category,
            'paymentMethod' => $paymentMethod,
            'preset' => $preset,
            'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
            'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'metrics' => [
                'total_income' => $totalPeriodIncome,
                'total_expense' => $totalPeriodExpense,
                'net_balance' => $netBalance,
                'income_count' => $incomeCount,
                'expense_count' => $expenseCount,
            ],
        ]);
    }

    /**
     * Store a newly created operational transaction (income or expense).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:income,expense'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'type.required' => 'Jenis transaksi wajib dipilih.',
            'type.in' => 'Jenis transaksi harus berupa pemasukan atau pengeluaran.',
            'title.required' => 'Judul / keperluan transaksi wajib diisi.',
            'category.required' => 'Kategori transaksi wajib dipilih.',
            'amount.required' => 'Nominal transaksi wajib diisi.',
            'amount.min' => 'Nominal transaksi minimal Rp 1.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $transaction = OperationalTransaction::create([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $typeLabel = $transaction->type === 'income' ? 'Pemasukan umum' : 'Pengeluaran umum';
        $formattedAmount = 'Rp ' . number_format($transaction->amount, 0, ',', '.');

        $redirectRoute = $transaction->type === 'income' ? 'operational.incomes' : 'operational.expenses';

        return redirect()->route($redirectRoute)
            ->with('success', "{$typeLabel} sebesar {$formattedAmount} berhasil ditambahkan!");
    }

    /**
     * Update the specified operational transaction in storage.
     */
    public function update(Request $request, OperationalTransaction $operationalTransaction): RedirectResponse
    {
        $user = Auth::user();
        $isAuthor = $user && $user->id === $operationalTransaction->user_id;
        $canManage = $user && ($user->can('manage operational') || $user->hasRole('admin'));

        if (!$isAuthor && !$canManage) {
            abort(403, 'Anda tidak memiliki wewenang untuk mengubah transaksi ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required' => 'Judul / keperluan transaksi wajib diisi.',
            'category.required' => 'Kategori transaksi wajib dipilih.',
            'amount.required' => 'Nominal transaksi wajib diisi.',
            'amount.min' => 'Nominal transaksi minimal Rp 1.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $operationalTransaction->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $redirectRoute = $operationalTransaction->type === 'income' ? 'operational.incomes' : 'operational.expenses';

        return redirect()->route($redirectRoute)
            ->with('success', "Data transaksi {$operationalTransaction->transaction_number} berhasil diperbarui.");
    }

    /**
     * Remove the specified operational transaction from storage.
     */
    public function destroy(OperationalTransaction $operationalTransaction): RedirectResponse
    {
        $user = Auth::user();
        $isAuthor = $user && $user->id === $operationalTransaction->user_id;
        $canManage = $user && ($user->can('manage operational') || $user->hasRole('admin'));

        if (!$isAuthor && !$canManage) {
            abort(403, 'Anda tidak memiliki wewenang untuk menghapus transaksi ini.');
        }

        $number = $operationalTransaction->transaction_number;
        $type = $operationalTransaction->type;
        $operationalTransaction->delete();

        $redirectRoute = $type === 'income' ? 'operational.incomes' : 'operational.expenses';

        return redirect()->route($redirectRoute)
            ->with('success', "Transaksi {$number} berhasil dihapus.");
    }

    /**
     * Export the filtered transactions to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->query('type', 'all');
        $search = $request->query('search');
        $category = $request->query('category');
        $dates = $this->resolveDateRange($request);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];

        $query = OperationalTransaction::with('user');

        if ($type !== 'all' && in_array($type, ['income', 'expense'])) {
            $query->where('type', $type);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
            ]);
        }

        if (filled($search)) {
            $query->search($search);
        }

        if (filled($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        $records = $query->latest('transaction_date')->latest('id')->get();

        $filename = 'rekap-operasional-' . ($type === 'all' ? 'semua' : $type) . '-' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($records, $type, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Header summary
            fputcsv($handle, ['REKAPITULASI KAS & BIAYA OPERASIONAL HOMESTAY']);
            fputcsv($handle, ['Tipe Transaksi', $type === 'income' ? 'Pemasukan Umum' : ($type === 'expense' ? 'Pengeluaran Umum' : 'Semua Transaksi')]);
            if ($startDate && $endDate) {
                fputcsv($handle, ['Periode', $startDate->format('d/m/Y') . ' s.d. ' . $endDate->format('d/m/Y')]);
            }
            fputcsv($handle, ['Total Data', $records->count()]);
            fputcsv($handle, ['Total Pemasukan (Rp)', number_format($records->where('type', 'income')->sum('amount'), 0, ',', '.')]);
            fputcsv($handle, ['Total Pengeluaran (Rp)', number_format($records->where('type', 'expense')->sum('amount'), 0, ',', '.')]);
            fputcsv($handle, ['Saldo Bersih (Rp)', number_format($records->where('type', 'income')->sum('amount') - $records->where('type', 'expense')->sum('amount'), 0, ',', '.')]);
            fputcsv($handle, []);

            // Columns
            fputcsv($handle, [
                'No. Transaksi',
                'Tipe',
                'Tanggal',
                'Judul / Uraian Transaksi',
                'Kategori',
                'Metode Pembayaran',
                'Nominal (Rp)',
                'Petugas Pencatat',
                'Role Petugas',
                'Catatan / Memo',
            ]);

            foreach ($records as $row) {
                $roleName = $row->user ? ($row->user->roles->first()?->name ?? 'Staff') : 'Sistem';
                fputcsv($handle, [
                    $row->transaction_number,
                    $row->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $row->transaction_date->format('d/m/Y'),
                    $row->title,
                    $row->category,
                    strtoupper($row->payment_method),
                    number_format($row->amount, 0, ',', '.'),
                    $row->user?->name ?? '-',
                    $roleName,
                    $row->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Resolve date range filter with presets.
     */
    protected function resolveDateRange(Request $request): array
    {
        $preset = $request->query('preset', 'this_month');

        switch ($preset) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::today();
                break;

            case 'last_7_days':
                $start = Carbon::today()->subDays(6);
                $end = Carbon::today();
                break;

            case 'this_month':
                $start = Carbon::today()->startOfMonth();
                $end = Carbon::today()->endOfMonth();
                break;

            case 'all':
                return [
                    'start_date' => null,
                    'end_date' => null,
                    'preset' => 'all',
                ];

            case 'custom':
                $rawStart = $request->query('start_date');
                $rawEnd = $request->query('end_date');

                $start = $rawStart ? Carbon::parse($rawStart) : Carbon::today()->startOfMonth();
                $end = $rawEnd ? Carbon::parse($rawEnd) : Carbon::today();
                break;

            default:
                $preset = 'this_month';
                $start = Carbon::today()->startOfMonth();
                $end = Carbon::today()->endOfMonth();
                break;
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'preset' => $preset,
        ];
    }
}

