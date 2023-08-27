<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Group;

class PurchaseReceiverResource extends JsonResource
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
            'user_id' => $this->receiver_id,
            'nickname' => Group::nicknameOf($this->group_id, $this->receiver_id),
            'balance' => round(floatval($this->amount), 2),
            'original_balance' => round(floatval($this->original_amount), 2),
            'custom_amount' => (bool)$this->custom_amount
        ];
    }
}
