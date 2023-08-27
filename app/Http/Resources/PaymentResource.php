<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReactionResource;
use App\Group;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'payment_id' => $this->id,
            'payer_id' => $this->payer_id,
            'payer_nickname' => Group::nicknameOf($this->group_id, $this->payer_id),
            'taker_id' => $this->taker_id,
            'taker_nickname' => Group::nicknameOf($this->group_id, $this->taker_id),
            'amount' => round(floatval($this->amount), 2),
            'original_amount' => round(floatval($this->original_amount), 2),
            'original_currency' => $this->original_currency ?? $this->group->currency,
            'category' => $this->category,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reactions' => ReactionResource::collection($this->reactions),
            'editable' => $this->editable
        ];
    }
}
