<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Member extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'nickname' => $this->member_data->nickname ?? $this->id,
            'balance' => $this->member_data->balance,
            'is_admin' => $this->member_data->is_admin
        ];
    }
}