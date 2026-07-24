<?php

namespace App\Http\Requests\Promo;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyPromoRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'promo_code' => ['required', 'string', 'max:50'],
            'booking_id' => ['nullable', 'uuid', 'exists:bookings,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'promo_code.required' => 'Kode promo wajib diisi.',
            'promo_code.max' => 'Kode promo maksimal 50 karakter.',
            'booking_id.exists' => 'Booking tidak ditemukan.',
        ];
    }
}
