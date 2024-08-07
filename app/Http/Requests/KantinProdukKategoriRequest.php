<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KantinProdukKategoriRequest extends FormRequest
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
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:255'],
        ];
    }

    public function update()
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:255'],
        ];
    }
}
