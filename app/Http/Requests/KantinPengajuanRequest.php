<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KantinPengajuanRequest extends FormRequest
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
            'kantin_id' => ['required', 'exists:kantin,id'],
            'jumlah_pengajuan' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in('pending', 'disetujui','ditolak')],
            'alasan_penolakan' => ['nullable', 'string', 'max:255'],
            'tanggal_pengajuan' => ['required', 'date'],
        ];
    }
}
