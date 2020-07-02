<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Payment extends JsonResource
{
    public function toArray($request)
    {
        return [
            'payment_id' => $this->id,
            'group_id' => $this->group_id,
            'group_name' => $this->group->name,
            'payer_id' => $this->payer_id,
            'payer_name' => $this->payer->name,
            'taker_id' => $this->taker_id,
            'taker_name' => $this->taker->name,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
