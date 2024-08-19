<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiswaLaundryRequest extends FormRequest
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
        return match ($this->route()->getName()) {
            'siswa-satuan-transaksi' => $this->satuan(),
            'siswa-kiloan-transaksi' => $this->kiloan(),
        };
    }

    public function satuan()
    {
        return [
            'item_detail' => ['required', 'array', 'min:1']
        ];
    }

    public function kiloan()
    {
        return [
            'berat' => ['required', 'numeric', 'min:1']
        ];
    }
}
