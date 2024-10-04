<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TopUpRequest extends FormRequest
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
        $rules = [
            'siswa_id' => ['exists:siswa,id'],
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required'],
        ];

        if (Auth::user()->role == 'OrangTua') {
            $rules['siswa_id' ][] = 'required';
        };

        return $rules;
    }
}