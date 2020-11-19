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
        $transaction = [
            'transaction_id' => $this->id,
            'name' => $this->name,
            //'group_id' => $this->group_id,
            //'group_name' => $this->group->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'buyer_id' => $this->buyer_id,
            'buyer_username' => (\App\User::find($this->buyer_id) != null ? \App\User::find($this->buyer_id)->username : "Deleted member"),
            'buyer_nickname' => ($this->group->members->find($this->buyer) != null ? $this->group->members->find($this->buyer)->member_data->nickname : "Deleted member"),
            'total_amount' => round(floatval($this->amount), 2),
        ];
        foreach ($this->receivers as $receiver) {
            $transaction['receivers'][] = [
                'user_id' => $receiver->receiver_id,
                'username' => ($receiver->user != null ? $receiver->user->username : "Deleted member"),
                'nickname' => ($this->group->members->find($receiver->user) != null ? $this->group->members->find($receiver->user)->member_data->nickname : "Deleted member"),
                'balance' => round(floatval($receiver->amount), 2)
            ];
        }
        return $transaction;
    }
}
