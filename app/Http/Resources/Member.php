<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Group;

class Member extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'username' => $this->username,
            'nickname' => $this->member_data->nickname,
            'balance' => round(floatval(Group::find($this->member_data->group_id)->balances()[$this->id]),2),
            'is_admin' => $this->member_data->is_admin,
            'is_guest' => $this->isGuest() ? 1 : 0
        ];
    }
}