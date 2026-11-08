<?php

namespace App\Http\Requests\Booking;

use App\Models\Customer;
use App\Services\BookingConflictService;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'deposit_amount' => (float)$this->input('deposit_amount', 0.00),
            'status' => $this->input('status', 'confirmed'),
            'payment_status' => $this->input('payment_status', 'unpaid'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
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
                if ($conflictService->hasConflict((int)$roomId, $checkIn, $checkOut)) {
                    $conflict = $conflictService->getConflictingBooking((int)$roomId, $checkIn, $checkOut);
                    $validator->errors()->add(
                        'room_id',
                        'Kamar ini telah dipesan untuk periode tersebut oleh kode reservasi: ' . ($conflict?->booking_code ?? 'Lain') . '.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Pilih data tamu terlebih dahulu.',
            'customer_id.exists' => 'Data tamu tidak ditemukan.',
            'room_id.required' => 'Pilih unit kamar yang akan dihuni.',
            'room_id.exists' => 'Kamar tidak valid.',
            'check_in_date.required' => 'Tanggal check-in wajib diisi.',
            'check_out_date.required' => 'Tanggal check-out wajib diisi.',
            'check_out_date.after' => 'Tanggal check-out harus setelah tanggal check-in.',
            'room_price_snapshot.required' => 'Tarif sewa kamar wajib terisi.',
        ];
    }
}

