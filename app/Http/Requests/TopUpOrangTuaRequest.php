<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpOrangTuaRequest extends FormRequest
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
            'get-payment-method' => $this->getPaymentMethod(),
            'request-transaksi' => $this->requestTransaksi(),
        };
    }

    public function getPaymentMethod()
    {
        return [
            'paymentAmount' => ['required', 'numeric', 'min:1']
        ];
    }

    public function requestTransaksi()
    {
        return [
            'siswa_id' => ['required', 'exists:siswa,id'],
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentMethod' => ['required'],
        ];
    }
}
