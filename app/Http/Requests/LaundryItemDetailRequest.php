<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaundryItemDetailRequest extends FormRequest
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
            'laundry_item_id' => ['required', 'exists:laundry_item,id'],
            'laundry_transaksi_satuan' => ['required', 'exists:laundry_transaksi_satuan,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ];
    }
}
