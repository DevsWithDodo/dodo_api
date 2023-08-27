<?php

namespace App\Http\Resources;

use App\Http\Resources\PurchaseReceiverResource;
use App\Http\Resources\ReactionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Group;

class PurchaseResource extends JsonResource
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
            'purchase_id' => $this->id,
            'name' => $this->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'buyer_id' => $this->buyer_id,
            'buyer_nickname' => Group::nicknameOf($this->group_id, $this->buyer_id),
            'total_amount' => round(floatval($this->amount), 2),
            'original_total_amount' => round(floatval($this->original_amount), 2),
            'original_currency' => $this->original_currency ?? $this->group->currency,
            'category' => $this->category,
            'receivers' => PurchaseReceiverResource::collection($this->receivers),
            'reactions' => ReactionResource::collection($this->reactions),
            'editable' => $this->editable
        ];
    }
}
