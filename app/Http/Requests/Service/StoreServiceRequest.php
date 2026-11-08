<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'in:meal,beverage,amenity,rental,laundry,other'],
            'sub_category' => ['nullable', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_stock' => ['nullable', 'boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_stock' => $this->boolean('has_stock'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'stock_quantity' => $this->boolean('has_stock') ? (int)$this->input('stock_quantity', 0) : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama item layanan / menu wajib diisi.',
            'category.required' => 'Kategori layanan wajib dipilih.',
            'price.required' => 'Harga jual item wajib diisi.',
            'price.numeric' => 'Harga jual harus berupa nilai numerik valid.',
        ];
    }
}

