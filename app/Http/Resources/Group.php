<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Member;

class Group extends JsonResource
{
    public function toArray($request)
    {
        return [
            'group_id' => $this->id,
            'group_name' => $this->name,
            'currency' => $this->currency,
            'admin_approval' => $this->admin_approval,
            'invitation' => $this->invitation,
            'boosted' => $this->boosted,
            'members' => Member::collection($this->members)
        ];
    }
}
