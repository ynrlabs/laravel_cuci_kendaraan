<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFcmTokenRequest extends FormRequest
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
            'fcm_token' => ['required', 'string', 'max:500'],
            'device_type' => ['nullable', 'string', 'in:android,ios,web'],
            'device_id' => ['nullable', 'string', 'max:255'],
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
            'fcm_token.required' => 'FCM token wajib diisi.',
            'fcm_token.string' => 'FCM token harus berupa teks.',
            'fcm_token.max' => 'FCM token terlalu panjang.',
            'device_type.in' => 'Device type tidak valid. Gunakan android, ios, atau web.',
        ];
    }
}