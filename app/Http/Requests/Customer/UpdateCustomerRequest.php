<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer') instanceof \App\Models\Customer
            ? $this->route('customer')->id
            : $this->route('customer');

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'identity_type' => ['required', 'in:ktp,paspor,sim,other'],
            'identity_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'identity_number')->ignore($customerId),
            ],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'special_notes' => ['nullable', 'string'],
            'is_blacklisted' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_blacklisted' => $this->boolean('is_blacklisted'),
            'country' => $this->filled('country') ? $this->input('country') : 'Indonesia',
        ]);
    }

    /**
     * Custom message for validation errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap tamu wajib diisi.',
            'identity_number.unique' => 'Nomor identitas / NIK sudah terdaftar di sistem.',
            'phone_number.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}

