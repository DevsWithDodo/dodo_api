<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Invitation extends JsonResource
{
    public function toArray($request)
    {
        return [
            'group_id' => $this->group_id,
            'group_name' => $this->group->name,
            'token' => $this->token,
            'usable_once_only' => $this->usable_once_only,
            'created_at' => $this->created_at
        ];
    }
}
