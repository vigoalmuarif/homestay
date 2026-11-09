<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type',
        'category',
        'title',
        'amount',
        'transaction_date',
        'payment_method',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    /**
     * Standard categories for Pemasukan Umum (General Income)
     */
    public static function defaultIncomeCategories(): array
    {
        return [
            'Parkir Tamu / Kendaraan',
            'Titip Bagasi / Penitipan',
            'Denda Kerusakan & Kehilangan',
            'Penjualan Kardus / Barang Bekas',
            'Sewa Fasilitas / Area',
            'Komisi Rekanan / Tour',
            'Pemasukan Lain-lain',
        ];
    }

    /**
     * Standard categories for Pengeluaran Umum (General Expense)
     */
    public static function defaultExpenseCategories(): array
    {
        return [
            'Bahan Pembersih & Deterjen',
            'Amenities & Perlengkapan Kamar',
            'Galon Air Minum & Gas',
            'Kebutuhan Dapur & Kopi/Teh',
            'Perbaikan & Pemeliharaan (Maintenance)',
            'Listrik, PDAM & Internet',
            'Transportasi & Kurir',
            'ATK & Perlengkapan Kantor',
            'Konsumsi Lembur Staf',
            'Pengeluaran Lain-lain',
        ];
    }

    /**
     * Generate sequential unique transaction number: e.g. OPR-IN-20261110-001 or OPR-EX-20261110-001
     */
    public static function generateTransactionNumber(string $type, $date = null): string
    {
        $prefix = $type === 'income' ? 'OPR-IN-' : 'OPR-EX-';
        $dateStr = Carbon::parse($date ?? now())->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        $count = self::whereDate('created_at', now())->count() + 1;
        return $prefix . $dateStr . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT) . '-' . $random;
    }

    protected static function booted(): void
    {
        static::creating(function (self $transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = self::generateTransactionNumber(
                    $transaction->type,
                    $transaction->transaction_date
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('transaction_number', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }
}

