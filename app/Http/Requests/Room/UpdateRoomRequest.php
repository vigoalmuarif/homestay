<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roomId = $this->route('room') instanceof \App\Models\Room
            ? $this->route('room')->id
            : $this->route('room');

        return [
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('rooms', 'room_number')->ignore($roomId),
            ],
            'status' => ['required', 'in:available,occupied,dirty,cleaning,maintenance'],
            'floor' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.required' => 'Tipe kamar wajib dipilih.',
            'room_type_id.exists' => 'Tipe kamar tidak valid.',
            'room_number.required' => 'Nomor kamar wajib diisi.',
            'room_number.unique' => 'Nomor kamar ini sudah terdaftar.',
            'status.required' => 'Status kamar wajib dipilih.',
        ];
    }
}

