<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KantinProdukRequest extends FormRequest
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
        return match ($this->method()) {
            'POST' => $this->store(),
            'PUT' => $this->update(),
        };
    }

    public function store()
    {
        return [
            'kantin_produk_kategori_id' => ['required', 'exists:kantin_produk_kategori,id'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'foto_produk' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ];
    }

    public function update()
    {
        return [
            'kantin_id' => ['required', 'exists:kantin,id'],
            'kantin_produk_kategori_id' => ['required', 'exists:kantin_produk_kategori,id'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'foto_produk' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in('aktif', 'tidak_aktif')],
        ];
    }
}
