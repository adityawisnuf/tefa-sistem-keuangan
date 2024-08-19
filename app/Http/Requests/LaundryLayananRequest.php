<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LaundryLayananRequest extends FormRequest
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
            'nama_layanan' => ['required', 'string', 'max:255'],
            'foto_layanan' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', Rule::in('satuan', 'kiloan')],
            'status' => ['nullable', Rule::in('aktif', 'tidak_aktif')],

        ];
    }

    public function update()
    {
        return [
            'nama_layanan' => ['required', 'string', 'max:255'],
            'foto_layanan' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', Rule::in('satuan', 'kiloan')],
            'status' => ['nullable', Rule::in('aktif', 'tidak_aktif')],
        ];
    }
}
