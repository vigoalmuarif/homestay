<?php

namespace App\Http\Requests\RoomType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tipe kamar wajib diisi.',
            'base_price.required' => 'Harga dasar sewa kamar wajib diisi.',
            'base_price.numeric' => 'Harga dasar harus berupa nilai numerik.',
            'capacity.required' => 'Kapasitas maksimal tamu wajib diisi.',
        ];
    }
}

