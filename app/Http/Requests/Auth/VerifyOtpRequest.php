<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class VerifyOtpRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'size:6', 'digits:6'],
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
            'phone.required' => 'Nomor telepon wajib diisi.',
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus 6 digit.',
            'otp.digits' => 'Kode OTP harus berupa angka.',
        ];
    }
}