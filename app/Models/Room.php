<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_type_id',
        'room_number',
        'status',
        'floor',
        'notes',
        'last_cleaned_at',
        'last_cleaned_by',
        'housekeeping_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_cleaned_at' => 'datetime',
    ];

    /**
     * Get the room type of this physical room.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Bookings assigned to this physical room.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Housekeeping tasks assigned to this room.
     */
    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class)->latest();
    }

    /**
     * Latest active housekeeping task.
     */
    public function activeHousekeepingTask()
    {
        return $this->hasOne(HousekeepingTask::class)->ofMany([
            'id' => 'max',
        ], function (Builder $query) {
            $query->whereIn('status', ['pending', 'in_progress']);
        });
    }

    /**
     * Last housekeeper who cleaned this room.
     */
    public function lastCleanedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_cleaned_by');
    }

    /**
     * Room status badge configuration.
     *
     * @return array{color: string, label: string}
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'available' => ['color' => 'success', 'label' => 'Bersih / Siap Huni'],
            'occupied' => ['color' => 'primary', 'label' => 'Terisi (In-House)'],
            'dirty' => ['color' => 'warning', 'label' => 'Kotor (Perlu Bersih)'],
            'cleaning' => ['color' => 'info', 'label' => 'Sedang Dibersihkan'],
            'maintenance' => ['color' => 'error', 'label' => 'Dalam Perbaikan'],
            default => ['color' => 'gray', 'label' => ucfirst($this->status)],
        };
    }

    /**
     * Scope only available rooms.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope only cleaning rooms.
     */
    public function scopeCleaning(Builder $query): Builder
    {
        return $query->where('status', 'cleaning');
    }

    /**
     * Scope only occupied rooms.
     */
    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope only dirty rooms needing housekeeping.
     */
    public function scopeDirty(Builder $query): Builder
    {
        return $query->where('status', 'dirty');
    }

    /**
     * Scope only rooms in maintenance.
     */
    public function scopeMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope search rooms by room number or floor.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('room_number', 'like', "%{$keyword}%")
                ->orWhere('floor', 'like', "%{$keyword}%")
                ->orWhereHas('roomType', function (Builder $typeQ) use ($keyword) {
                    $typeQ->where('name', 'like', "%{$keyword}%");
                });
        });
    }
}
