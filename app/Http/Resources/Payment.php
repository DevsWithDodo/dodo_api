<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Reaction;

class Payment extends JsonResource
{
    public function toArray($request)
    {
        $this->load('group.members');
        $payer = $this->group->member($this->payer_id);
        $taker = $this->group->member($this->taker_id);
        return [
            'payment_id' => $this->id,
            'payer_id' => $this->payer_id,
            'payer_username' => ($payer ? $payer->username : '$$deleted_member$$'),
            'payer_nickname' => ($payer ? $payer->member_data->nickname : '$$deleted_member$$'),
            'taker_id' => $this->taker_id,
            'taker_username' => ($taker ? $taker->username : '$$deleted_member$$'),
            'taker_nickname' => ($taker ? $taker->member_data->nickname : '$$deleted_member$$'),
            'amount' => round(floatval($this->amount), 2),
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reactions' => Reaction::collection($this->reactions)
        ];
    }
}
