<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaundryItemRequest extends FormRequest
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
        return match ($this->method) {
            'POST' => $this->store(),
            'PUT' => $this->update(),
        };
    }

    public function store()
    {
        return [
            'laundry_id' => ['required', 'exists:laundry,id'],
            'nama_item' => ['required', 'string', 'max:255'],
            'foto_item' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
        ]
    }

    public function update()
    {
        return [
            'laundry_id' => ['required', 'exists:laundry,id'],
            'nama_item' => ['required', 'string', 'max:255'],
            'foto_item' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'deskripsi' => ['required', 'string'],
            'harga' => ['required', 'integer', 'min:0'],
        ]
    }
}
