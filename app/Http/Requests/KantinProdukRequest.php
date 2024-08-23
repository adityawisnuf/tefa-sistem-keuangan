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
            'harga_pokok' => ['required', 'integer', 'min:0'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'stok' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function update()
    {
        return [
            'kantin_produk_kategori_id' => ['sometimes','required', 'exists:kantin_produk_kategori,id'],
            'nama_produk' => ['sometimes', 'required', 'string', 'max:255'],
            'foto_produk' => ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['sometimes', 'required', 'string'],
            'harga_pokok' => ['sometimes', 'required', 'integer', 'min:0'],
            'harga_jual' => ['sometimes', 'required', 'integer', 'min:0'],
            'stok' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'nullable', Rule::in('aktif', 'tidak_aktif')],
        ];
    }
}
