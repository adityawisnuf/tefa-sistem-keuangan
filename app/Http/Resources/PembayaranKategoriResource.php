<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PembayaranKategoriResource extends JsonResource
{
    public $status;

    public $message;

    /**
     * __construct
     *
     * @param  bool  $status
     * @param  string  $message
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $status = true, $message = '')
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'success' => $this->status,
            'message' => $this->message,
            'data' => $this->resource,
        ];
    }
}
