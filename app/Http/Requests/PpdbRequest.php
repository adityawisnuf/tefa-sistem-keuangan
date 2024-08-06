<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PpdbRequest extends FormRequest
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
            'nama_depan' => 'required|string|max:255',
             'nama_belakang' => 'required|string|max:255',
             'jenis_kelamin' => 'required|string|max:10',
             'nik' => 'required|integer|unique:pendaftar',
             'email' => 'required|string|email|max:255',
             'nisn' => 'required|integer|unique:pendaftar',
             'tempat_lahir' => 'required|string|max:255',
             'tgl_lahir' => 'required|date',
             'alamat' => 'required|string',
             'village_id' => 'required|integer|exists:villages,id',
             'nama_ayah' => 'required|string|max:255',
             'nama_ibu' => 'required|string|max:255',
             'tgl_lahir_ayah' => 'required|date',
             'tgl_lahir_ibu' => 'required|date',
             'akte_kelahiran' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
             'kartu_keluarga' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
             'ijazah' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
             'raport' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
             'sekolah_asal' => 'string|max:255',
             'tahun_lulus' => 'string|date',
             'jurusan_tujuan' => 'string|max:255',

        ];
    }
}
