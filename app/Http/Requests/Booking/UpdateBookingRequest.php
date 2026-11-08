<?php

namespace App\Http\Requests\Booking;

use App\Models\Customer;
use App\Services\BookingConflictService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'room_id' => ['required', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'room_price_snapshot' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,confirmed,checked_in,checked_out,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,partial,paid'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $booking = $this->route('booking');
            $bookingId = $booking ? $booking->id : null;

            $customerId = $this->input('customer_id');
            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer && $customer->is_blacklisted) {
                    $validator->errors()->add('customer_id', 'Tamu atas nama ' . $customer->full_name . ' berada dalam status Blacklist.');
                }
            }

            $roomId = $this->input('room_id');
            $checkIn = $this->input('check_in_date');
            $checkOut = $this->input('check_out_date');

            if ($roomId && $checkIn && $checkOut && $checkOut > $checkIn) {
                $conflictService = app(BookingConflictService::class);
                if ($conflictService->hasConflict((int)$roomId, $checkIn, $checkOut, $bookingId)) {
                    $conflict = $conflictService->getConflictingBooking((int)$roomId, $checkIn, $checkOut, $bookingId);
                    $validator->errors()->add(
                        'room_id',
                        'Kamar ini telah dipesan oleh reservasi lain (' . ($conflict?->booking_code ?? 'Lain') . ') pada periode tersebut.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Pilih data tamu terlebih dahulu.',
            'room_id.required' => 'Pilih unit kamar.',
            'check_in_date.required' => 'Tanggal check-in wajib diisi.',
            'check_out_date.required' => 'Tanggal check-out wajib diisi.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
            'room_price_snapshot.required' => 'Tarif sewa kamar wajib terisi.',
        ];
    }
}

