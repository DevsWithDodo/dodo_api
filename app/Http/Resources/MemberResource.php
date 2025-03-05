<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'username' => $this->username,
            'nickname' => $this->member_data->nickname,
            'balance' => floatval($this->member_data->balance),
            'is_admin' => $this->member_data->is_admin,
            'is_guest' => $this->is_guest ? 1 : 0,
            'payment_details' => $this->payment_details,
        ];
    }
}
