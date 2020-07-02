<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Purchase extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'purchase_id' => $this->id,
            'purchase_name' => $this->name,
            'created_at' => $this->created_at,
            'modified_at' => $this->modified_at
        ];
    }
}
