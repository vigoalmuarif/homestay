<?php

namespace App\Http\Requests\Inclusion;

use Illuminate\Foundation\Http\FormRequest;

class StoreInclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'exists:room_types,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'category' => ['required', 'in:meal,beverage,amenity,rental,laundry,any'],
            'session_time' => ['nullable', 'string', 'max:50'],
            'allowance_limit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allowance_limit' => (float)$this->input('allowance_limit', 0.00),
        ]);
    }

    public function messages(): array
    {
        return [
            'room_type_id.required' => 'Tipe kamar wajib dipilih.',
            'category.required' => 'Kategori inklusi paket wajib dipilih.',
        ];
    }
}

