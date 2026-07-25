<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SlotRequest extends FormRequest
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
            'date' => ['required', 'date', 'after_or_equal:today'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
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
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'date.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'lat.numeric' => 'Latitude harus berupa angka.',
            'lat.between' => 'Latitude tidak valid.',
            'lng.numeric' => 'Longitude harus berupa angka.',
            'lng.between' => 'Longitude tidak valid.',
            'radius.numeric' => 'Radius harus berupa angka.',
            'radius.min' => 'Radius minimal 0.1 km.',
            'radius.max' => 'Radius maksimal 100 km.',
        ];
    }
}