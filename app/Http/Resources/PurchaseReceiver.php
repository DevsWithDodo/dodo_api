<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Group;

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
        return [
            'user_id' => $this->receiver_id,
            //'username' => $this->receiver->username ?? '$$deleted_member$$',
            'nickname' => Group::nicknameOf($this->group_id, $this->receiver_id) ?? '$$deleted_member$$',
            'balance' => round(floatval($this->amount), 2)
        ];
    }
}
