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
        $this->load('group.members');
        $buyer = $this->group->members->find($this->buyer);
        $transaction = [
            'transaction_id' => $this->id,
            'name' => $this->name,
            //'group_id' => $this->group_id,
            //'group_name' => $this->group->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'buyer_id' => $this->buyer_id,
            'buyer_username' => ($buyer ? $buyer->username : '$$deleted_member$$'),
            'buyer_nickname' => ($buyer ? $buyer->member_data->nickname : '$$deleted_member$$'),
            'total_amount' => round(floatval($this->amount), 2),
        ];
        foreach ($this->receivers as $receiver) {
            $receiver_user = $this->group->members->find($receiver->user);
            $transaction['receivers'][] = [
                'user_id' => $receiver->receiver_id,
                'username' => ($receiver_user ? $receiver_user->username : '$$deleted_member$$'),
                'nickname' => ($receiver_user ? $receiver_user->member_data->nickname : '$$deleted_member$$'),
                'balance' => round(floatval($receiver->amount), 2)
            ];
        }
        return $transaction;
    }
}
