<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Member extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'username' => $this->username,
            'nickname' => $this->member_data->nickname,
            'balance' => floatval($this->member_data->balance),
            'is_admin' => $this->member_data->is_admin,
            'is_guest' => $this->isGuest() ? 1 : 0
        ];
    }
}
