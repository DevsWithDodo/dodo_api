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
            'payer_nickname' => $this->group->members->find($this->payer_id)->member_data->nickname,
            'taker_id' => $this->taker_id,
            'taker_nickname' => $this->group->members->find($this->taker_id)->member_data->nickname,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
