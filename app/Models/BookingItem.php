<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_id',
        'item_name',
        'quantity',
        'unit_price',
        'discount_or_allowance',
        'subtotal',
        'billing_type',
        'status',
        'is_free_inclusion',
        'notes',
        'served_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_or_allowance' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_free_inclusion' => 'boolean',
    ];

    /**
     * Booking associated with this item.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Service master data.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Staff member who served or created this order.
     */
    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    /**
     * Scope for items charged to the room folio.
     */
    public function scopeChargedToRoom($query)
    {
        return $query->where('billing_type', 'charged_to_room');
    }

    /**
     * Scope for items paid instantly on the spot.
     */
    public function scopePaidInstantly($query)
    {
        return $query->where('billing_type', 'paid_instantly');
    }

    /**
     * Scope for items that are complimentary room package inclusions.
     */
    public function scopeFreeInclusions($query)
    {
        return $query->where('is_free_inclusion', true);
    }

    /**
     * Scope for active (non-cancelled) items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
}

