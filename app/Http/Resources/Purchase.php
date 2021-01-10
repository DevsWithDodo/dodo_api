<?php

namespace App\Http\Resources;

use App\Http\Resources\PurchaseReceiver;
use App\Http\Resources\Reaction;
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
        $this->load(['group.members', 'receivers.purchase.group.members']);
        $buyer = $this->group->members->find($this->buyer);
        return [
            'transaction_id' => $this->id,
            'name' => $this->name,
            //'group_id' => $this->group_id,
            //'group_name' => $this->group->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'buyer_id' => $this->buyer_id,
            'buyer_username' => $buyer?->username ?? '$$deleted_member$$',
            'buyer_nickname' => $buyer?->member_data->nickname ?? '$$deleted_member$$',
            'total_amount' => round(floatval($this->amount), 2),
            'receivers' => PurchaseReceiver::collection($this->receivers),
            'reactions' => Reaction::collection($this->reactions)
        ];
    }
}
