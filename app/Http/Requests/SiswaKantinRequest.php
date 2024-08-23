<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiswaKantinRequest extends FormRequest
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
        return [
            'detail_pesanan' => ['required', 'array', 'min:1'],
            'detail_pesanan.*.kantin_produk_id' => ['required', 'exists:kantin_produk,id'],
            'detail_pesanan.*.jumlah' => ['required', 'numeric', 'min:1'],
        ];
    }
}