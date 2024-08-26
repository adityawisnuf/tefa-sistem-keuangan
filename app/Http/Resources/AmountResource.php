<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AmountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'paymentAmount' => $this->paymentAmount, // Ini akan diproses oleh accessor model
            'created_at' => Carbon::parse($this->created_at)->format('d/m/Y'),
            'updated_at' => Carbon::parse($this->updated_at)->format('d/m/Y'),
        ];
    }
}
