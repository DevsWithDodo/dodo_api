<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReceiver extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $receiver_user = $this->purchase->group->members->find($this->user);
        return [
            'user_id' => $this->receiver_id,
            'username' => $this->receiver->username ?? '$$deleted_member$$',
            'nickname' => $receiver_user?->member_data->nickname ?? '$$deleted_member$$',
            'balance' => round(floatval($this->amount), 2)
        ];
    }
}
