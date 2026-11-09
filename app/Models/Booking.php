<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'booking_code',
        'customer_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'total_nights',
        'room_price_snapshot',
        'deposit_amount',
        'status',
        'payment_status',
        'notes',
        'actual_check_in_at',
        'actual_check_out_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_date' => 'datetime',
            'check_out_date' => 'datetime',
            'room_price_snapshot' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'total_nights' => 'integer',
            'actual_check_in_at' => 'datetime',
            'actual_check_out_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (Booking $booking) {
            $nights = max(1, (int) ($booking->total_nights ?: 1));
            $roomRate = (float) ($booking->room_price_snapshot ?? 0);
            $roomCharge = $roomRate * $nights;
            $extraCharges = $booking->exists ? $booking->total_extra_charges : 0.00;
            $grossTotal = $roomCharge + $extraCharges;
            $deposit = (float) ($booking->deposit_amount ?? 0.00);
            $balanceDue = max(0.00, $grossTotal - $deposit);

            if ($balanceDue <= 0.00 && ($deposit > 0 || $grossTotal == 0)) {
                $booking->payment_status = 'paid';
            } elseif ($deposit > 0 && $balanceDue > 0) {
                $booking->payment_status = 'partial';
            } elseif ($deposit <= 0.00 && $grossTotal > 0 && $booking->payment_status !== 'paid') {
                $booking->payment_status = 'unpaid';
            }
        });
    }

    /**
     * Generate unique booking code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Customer associated with the booking.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Room associated with the booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Booking items / mini-pos room orders.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Calculated total room rental charges.
     */
    public function getTotalRoomChargeAttribute(): float
    {
        return (float) ($this->room_price_snapshot * $this->total_nights);
    }

    /**
     * Total extra charges from items charged to the room folio (excluding cancelled).
     */
    public function getTotalExtraChargesAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items
                ->where('billing_type', 'charged_to_room')
                ->where('status', '!=', 'cancelled')
                ->sum('subtotal');
        }

        return (float) $this->items()
            ->where('billing_type', 'charged_to_room')
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');
    }

    /**
     * Total grand bill: Room Charges + Extra Add-on Charges.
     */
    public function getGrandTotalAttribute(): float
    {
        return $this->total_room_charge + $this->total_extra_charges;
    }

    /**
     * Balance due (Sisa Tagihan) = Grand Total - Deposit.
     */
    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->grand_total - (float) $this->deposit_amount);
    }

    /**
     * Compute and synchronize payment status based on current deposit vs grand total.
     */
    public function syncPaymentStatus(bool $save = true): string
    {
        $deposit = (float) $this->deposit_amount;
        $grossTotal = (float) $this->grand_total;
        $balanceDue = max(0.00, $grossTotal - $deposit);

        if ($balanceDue <= 0.00 && ($deposit > 0 || $grossTotal == 0)) {
            $status = 'paid';
        } elseif ($deposit > 0 && $balanceDue > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        if ($this->payment_status !== $status) {
            $this->payment_status = $status;
            if ($save && $this->exists) {
                $this->saveQuietly();
            }
        }

        return $status;
    }

    /**
     * Scope bookings for in-house guests (checked_in).
     */
    public function scopeInHouse(Builder $query): Builder
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Scope bookings arriving today.
     */
    public function scopeArrivingToday(Builder $query): Builder
    {
        return $query->whereDate('check_in_date', Carbon::today())
            ->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Scope bookings departing today.
     */
    public function scopeDepartingToday(Builder $query): Builder
    {
        return $query->whereDate('check_out_date', Carbon::today())
            ->where('status', 'checked_in');
    }

    /**
     * Scope active reservations that block room calendar.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    /**
     * Scope search query across booking code, guest name, phone, or room number.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('booking_code', 'like', "%{$keyword}%")
                ->orWhereHas('customer', function (Builder $cq) use ($keyword) {
                    $cq->where('full_name', 'like', "%{$keyword}%")
                        ->orWhere('phone_number', 'like', "%{$keyword}%");
                })
                ->orWhereHas('room', function (Builder $rq) use ($keyword) {
                    $rq->where('room_number', 'like', "%{$keyword}%");
                });
        });
    }

    /**
     * Helper to get status badge Tailwind classes.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'checked_in' => [
                'label' => 'In-House (Check-In)',
                'class' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
            ],
            'confirmed' => [
                'label' => 'Terkonfirmasi',
                'class' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400 border border-brand-200 dark:border-brand-800',
            ],
            'checked_out' => [
                'label' => 'Selesai (Check-Out)',
                'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
            ],
            'pending' => [
                'label' => 'Menunggu Konfirmasi',
                'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
            ],
            default => [
                'label' => ucfirst($this->status),
                'class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ],
        };
    }
}

