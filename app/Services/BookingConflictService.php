<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingConflictService
{
    /**
     * Check if a room has conflicting reservations during the specified date range.
     * Overlap rule: (existing.check_in_date < new.check_out_date) AND (existing.check_out_date > new.check_in_date)
     * Statuses excluded: 'cancelled', 'checked_out'
     */
    public function hasConflict(int $roomId, string $checkInDate, string $checkOutDate, ?int $excludeBookingId = null): bool
    {
        return $this->buildConflictQuery($roomId, $checkInDate, $checkOutDate, $excludeBookingId)->exists();
    }

    /**
     * Get the conflicting booking record if any.
     */
    public function getConflictingBooking(int $roomId, string $checkInDate, string $checkOutDate, ?int $excludeBookingId = null): ?Booking
    {
        return $this->buildConflictQuery($roomId, $checkInDate, $checkOutDate, $excludeBookingId)->first();
    }

    /**
     * Get all rooms available for the specified date range.
     */
    public function getAvailableRooms(string $checkInDate, string $checkOutDate, ?int $roomTypeId = null, ?int $excludeBookingId = null): Collection
    {
        $checkIn = Carbon::parse($checkInDate)->format('Y-m-d');
        $checkOut = Carbon::parse($checkOutDate)->format('Y-m-d');

        $query = Room::with('roomType')
            ->where('status', '!=', 'maintenance');

        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }

        $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut, $excludeBookingId) {
            $q->whereNotIn('status', ['cancelled', 'checked_out'])
                ->whereDate('check_in_date', '<', $checkOut)
                ->whereDate('check_out_date', '>', $checkIn);

            if ($excludeBookingId) {
                $q->where('id', '!=', $excludeBookingId);
            }
        });

        return $query->orderBy('room_number')->get();
    }

    /**
     * Calculate stay duration in nights.
     */
    public function calculateNights(string $checkInDate, string $checkOutDate): int
    {
        $in = Carbon::parse($checkInDate)->startOfDay();
        $out = Carbon::parse($checkOutDate)->startOfDay();

        $diff = $in->diffInDays($out, false);

        return (int) max(1, $diff);
    }

    /**
     * Build base query to detect date conflict on a room.
     */
    protected function buildConflictQuery(int $roomId, string $checkInDate, string $checkOutDate, ?int $excludeBookingId = null)
    {
        $checkIn = Carbon::parse($checkInDate)->format('Y-m-d');
        $checkOut = Carbon::parse($checkOutDate)->format('Y-m-d');

        $query = Booking::query()
            ->where('room_id', $roomId)
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->whereDate('check_in_date', '<', $checkOut)
            ->whereDate('check_out_date', '>', $checkIn);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query;
    }
}

