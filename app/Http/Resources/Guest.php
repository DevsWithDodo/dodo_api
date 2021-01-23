<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Guest extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'username' => $this->member_data->nickname,
            'api_token' => $this->api_token,
        ];
    }
}
