<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Reaction;
use App\Group;

class Payment extends JsonResource
{
    public function toArray($request)
    {
        switch ($this->note) {
            case '$$legacy_money$$':
                $note = __('general.legacy_money');
                break;
            case '$$auto_payment$$':
                $note = __('general.auto_payment');
            default:
                $note = $this->note;
                break;
        }
        return [
            'payment_id' => $this->id,
            'payer_id' => $this->payer_id,
            'payer_nickname' => Group::nicknameOf($this->group_id, $this->payer_id),
            'taker_id' => $this->taker_id,
            'taker_nickname' => Group::nicknameOf($this->group_id, $this->taker_id),
            'amount' => round(floatval($this->amount), 2),
            'note' => $note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reactions' => Reaction::collection($this->reactions)
        ];
    }
}
