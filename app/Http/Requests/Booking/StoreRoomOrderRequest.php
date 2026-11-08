<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'billing_type' => ['required', 'in:charged_to_room,paid_instantly'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'service_id.required' => 'Pilih item layanan atau menu.',
            'service_id.exists' => 'Layanan yang dipilih tidak valid.',
            'quantity.required' => 'Jumlah pesanan wajib diisi.',
            'quantity.min' => 'Jumlah pesanan minimal 1.',
            'billing_type.required' => 'Pilih metode penagihan.',
            'billing_type.in' => 'Metode penagihan harus dibebankan ke kamar atau bayar langsung.',
        ];
    }
}

