<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Member extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->member_data->nickname ?? $this->name,
            'balance' => $this->member_data->balance,
            'is_admin' => $this->member_data->is_admin
        ];
    }
}