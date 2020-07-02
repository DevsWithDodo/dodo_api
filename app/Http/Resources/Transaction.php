<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) //Purchase
    {
        $transaction = [
            'name' => $this->name,
            //'group_id' => $this->group_id,
            'group' =>$this->group->name,
        ];
        foreach ($this->buyers as $buyer) {
            $transaction['buyers'][] = [
                //'user_id' => $buyer->buyer_id,
                'user' => $buyer->user->name,
                'amount' => $buyer->amount
            ];
        }
        foreach ($this->receivers as $receiver) {
            $transaction['receivers'][] = [
                //'user_id' => $receiver->receiver_id,
                'user' => $receiver->user->name,
                'amount' => $receiver->amount
            ];
        }

        return $transaction;
    }
}
